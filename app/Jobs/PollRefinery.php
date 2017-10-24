<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\MiningActivity;
use App\Jobs\MinerCheck;
use App\Miner;

class PollRefinery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $observer_id;
    private $page;
    private $total_pages;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $page = 1)
    {

        $esi = new EsiConnection;

        $this->observer_id = $id;
        $this->page = $page;
       
        // If this is the first page request, we need to check for multiple pages and generate subsequent jobs.
        if ($page == 1)
        {
            // This raw curl request can be replaced with an $esi call once the Eseye library is updated to return response headers.
            $url = 'https://esi.tech.ccp.is/latest/corporation/' . $esi->corporation_id . '/mining/observers/' . $id . '/?datasource=singularity&token=' . $esi->token;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, "extractXPagesHeader"));
            $body = curl_exec($ch);
            if ($this->total_pages > 1)
            {
                for ($i = 2; $i <= $this->total_pages; $i++)
                {
                    PollRefinery::dispatch($refinery->observer_id, $i);
                }
            }
        }
        
    }

    /**
     * Grab the X-Pages header out of the response header.
     */
    private function extractXPagesHeader($curl, $header)
    {
        if (stristr($header, 'X-Pages'))
        {
            preg_match('/\d+/', $header, $matches);
            if (count($matches))
            {
                $this->total_pages = $matches[0];
            }
        }
        return strlen($header);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $esi = new EsiConnection;

        // Retrieve the mining activity log page for this refinery.
        $activity_log = $esi->esi->invoke('get', '/corporation/{corporation_id}/mining/observers/{observer_id}/', [
            'corporation_id' => $esi->corporation_id,
            'observer_id' => $this->observer_id,
            'page' => $this->page,
        ]);

        foreach ($activity_log as $log_entry)
        {
            // Check whether this entry has already been recorded.
            $existing_activity = MiningActivity::where([
                'miner_id' => $log_entry->character_id,
                'refinery_id' => $this->observer_id,
                'type_id' => $log_entry->type_id,
                'quantity' => $log_entry->quantity,
            ])->first();
            if (!isset($existing_activity))
            {
                // Create a new entry in the database for this activity.
                $mining_activity = new MiningActivity;
                $mining_activity->miner_id = $log_entry->character_id;
                $mining_activity->refinery_id = $this->observer_id;
                $mining_activity->type_id = $log_entry->type_id;
                $mining_activity->quantity = $log_entry->quantity;
                $mining_activity->save();
                // Check if this miner is already known.
                $miner = Miner::where('eve_id', $log_entry->character_id)->first();
                // If not, create a job to add the new miner entry.
                if (!isset($miner))
                {
                    MinerCheck::dispatch($log_entry->character_id);
                }
                // Check if this ore type exists in the taxes table.
                $tax_rate = TaxRate::where('type_id', $log_entry->type_id)->first();
                // If not, create and insert it with zero values.
                if (!isset($tax_rate))
                {
                    $tax_rate = new TaxRate;
                    $tax_rate->type_id = $log_entry->type_id;
                    $tax_rate->check_materials = 1;
                    $tax_rate->value = 0;
                    $tax_rate->tax_rate = 0;
                    $tax_rate->updated_by = 0;
                    $tax_rate->save();
                }
            }
        }
        
    }

}
