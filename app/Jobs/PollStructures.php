<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Refinery;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PollStructures implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    private $page;
    private $total_pages = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($page = 1)
    {
        $esi = new EsiConnection;
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
            $url = 'https://esi.tech.ccp.is/latest/corporations/' . $esi->corporation_id . '/structures/?datasource=' . env('ESEYE_DATASOURCE', 'tranquility') . '&token=' . $esi->token;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, "extractXPagesHeader"));
            $body = curl_exec($ch);
            if ($this->total_pages > 1)
            {
                Log::info('PollStructures: found more than 1 page of corporation structures, queuing additional jobs for ' . $this->total_pages . ' total pages');
                $delay_counter = 1;
                for ($i = 2; $i <= $this->total_pages; $i++)
                {
                    PollStructures::dispatch($i)->delay(Carbon::now()->addMinutes($delay_counter));
                    $delay_counter++;
                }
            }
        }

        Log::info('PollStructures: requesting corporation structures, page ' . $this->page);

        // Request all corporation structures of the prime user's corporation.
        $structures = $esi->esi->setQueryString([
            'page' => $this->page,
        ])->invoke('get', '/corporations/{corporation_id}/structures/', [
            'corporation_id' => $esi->corporation_id,
        ]);

        // Loop through all the structures, looking for Athanors or Tataras.
        $refineries = array(
            35835, // Athanor
            35836, // Tatara
        );
        $delay_counter = 1;
        foreach ($structures as $structure)
        {
            if (in_array($structure->type_id, $refineries))
            {
                // Found a refinery. If it doesn't already exist, create a record for it.
                $refinery = Refinery::where('observer_id', $structure->structure_id)->first();
                if (!isset($refinery))
                {
                    $refinery = new Refinery;
                    $refinery->observer_id = $structure->structure_id;
                    $refinery->observer_type = 'structure';
                    $refinery->save();
                    Log::info('PollStructures: created new refinery record for ' . $structure->structure_id);
                }
                // Create a new job to fetch or update the parts we don't get from this response.
                PollStructureData::dispatch($structure->structure_id)->delay(Carbon::now()->addMinutes($delay_counter));
                $delay_counter++;
            }
        }

    }

}
