<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Refinery;
use App\Jobs\PollStructureData;
use Illuminate\Support\Facades\Log;

class PollRefineries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $esi = new EsiConnection;

        // Clear out all claim details for refinery detonations that have happened in the last 24 hours.
        $previous_detonations = DB::update('UPDATE refineries SET claimed_by_primary = NULL, claimed_by_secondary = NULL, updated_at = \'' . date('Y-m-d H:i:s') . '\' WHERE natural_decay_time < NOW() - INTERVAL 1 DAY AND (claimed_by_primary IS NOT NULL OR claimed_by_secondary IS NOT NULL)');
        Log::info('PollRefineries: cleared ' . $previous_detonations . ' claimed refinery detonations from the previous 24 hours');

        // Request a list of all of the active mining observers belonging to the corporation.
        $mining_observers = $esi->esi->invoke('get', '/corporation/{corporation_id}/mining/observers/', [
            'corporation_id' => $esi->corporation_id,
        ]);

        Log::info('PollRefineries: found ' . count($mining_observers) . ' refineries with active asteroid fields');

        // Process the refineries list. For each entry, we want to check and see if it already exists 
        // in the database. If it doesn't, we create a new database entry for it.
        foreach ($mining_observers as $observer)
        {
            $refinery = Refinery::where('observer_id', $observer->observer_id)->first();
            if (!isset($refinery))
            {
                $refinery = new Refinery;
                $refinery->observer_id = $observer->observer_id;
                $refinery->observer_type = $observer->observer_type;
                $refinery->save();
                Log::info('PollRefineries: created new refinery record for ' . $observer->observer_id);
                // Create a new job to fill in the parts we don't know from this response.
                PollStructureData::dispatch($observer->observer_id);
            }
        }

    }
}
