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
use App\TaxRate;
use App\Miner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PollRefinery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;
    private $observer_id;
    private $page;
    private $total_pages = 1;

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

        // If this is the first page request, we need to check for multiple pages and generate subsequent jobs.
        if ($this->page == 1)
        {
            // This raw curl request can be replaced with an $esi call once the Eseye library is updated to return response headers.
            $url = 'https://esi.tech.ccp.is/latest/corporation/' . $esi->corporation_id . '/mining/observers/' . $this->observer_id . '/?datasource=' . env('ESEYE_DATASOURCE', 'tranquility') . '&token=' . $esi->token;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, "extractXPagesHeader"));
            $body = curl_exec($ch);
            if ($this->total_pages > 1)
            {
                Log::info('PollRefinery: found more than 1 page of mining data, queuing additional jobs for ' . $this->total_pages . ' total pages');
                $delay_counter = 1;
                for ($i = 2; $i <= $this->total_pages; $i++)
                {
                    PollRefinery::dispatch($this->observer_id, $i)->delay(Carbon::now()->addMinutes($delay_counter));
                    $delay_counter++;
                }
            }
        }
        
        Log::info('PollRefinery: requesting mining activity log for refinery ' . $this->observer_id . ', page ' . $this->page);

        // Retrieve the mining activity log page for this refinery.
        $activity_log = $esi->esi->setQueryString([
            'page' => $this->page,
        ])->invoke('get', '/corporation/{corporation_id}/mining/observers/{observer_id}/', [
            'corporation_id' => $esi->corporation_id,
            'observer_id' => $this->observer_id,
        ]);

        Log::info('PollRefinery: received ' . count($activity_log) . ' mining records');

        $new_mining_activity_records = array();
        $miner_ids = array();
        $type_ids = array();

        foreach ($activity_log as $log_entry)
        {

            $hash = hash('sha1', $log_entry->character_id . $this->observer_id . $log_entry->type_id . $log_entry->quantity . $log_entry->last_updated);

            // Add a new mining activity array to the list.
            $new_mining_activity_records[] = [
                'hash' => $hash,
                'miner_id' => $log_entry->character_id,
                'refinery_id' => $this->observer_id,
                'type_id' => $log_entry->type_id,
                'quantity' => $log_entry->quantity,
                'created_at' => $log_entry->last_updated . ' 00:00:00',
                'updated_at' => $log_entry->last_updated . ' 00:00:00',
            ];

            // Store the miner.
            if (!in_array($log_entry->character_id, $miner_ids))
            {
                $miner_ids[] = $log_entry->character_id;
            }

            // Store the ore type.
            if (!in_array($log_entry->type_id, $type_ids))
            {
                $type_ids[] = $log_entry->type_id;
            }

        }

        // Insert all of the new mining activity records to the database.
        MiningActivity::insertIgnore($new_mining_activity_records);

        Log::info('PollRefinery: inserted up to ' . count($new_mining_activity_records) . ' new mining activity records');
        
        // Check if this miner is already known.
        foreach ($miner_ids as $miner_id)
        {
            $miner = Miner::where('eve_id', $miner_id)->first();
            // If not, create a job to add the new miner entry.
            $delay_counter = 1;
            if (!isset($miner))
            {
                Log::info('PollRefinery: unknown miner found, queuing job to retrieve details');
                MinerCheck::dispatch($miner_id)->delay(Carbon::now()->addSeconds($delay_counter * 5));
                $delay_counter++;
            }
        }

        // Check if this ore type exists in the taxes table.
        foreach ($type_ids as $type_id)
        {
            $tax_rate = TaxRate::where('type_id', $type_id)->first();
            // If not, create and insert it with zero values.
            if (!isset($tax_rate))
            {
                $tax_rate = new TaxRate;
                $tax_rate->type_id = $type_id;
                $tax_rate->check_materials = 1;
                $tax_rate->value = 0;
                $tax_rate->tax_rate = 7;
                $tax_rate->updated_by = 0;
                $tax_rate->save();
                Log::info('PollRefinery: unknown ore ' . $type_id . ' found, new tax rate record created');
            }
        }

    }

}
