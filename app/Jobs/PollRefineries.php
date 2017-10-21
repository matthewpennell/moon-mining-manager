<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Refinery;

class PollRefineries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $esi = new EsiConnection;

        // Request a list of all of the active mining observers belonging to the corporation.
        $mining_observers = $esi->esi->invoke('get', '/corporation/{corporation_id}/mining/observers/', [
            'corporation_id' => $esi->corporation_id,
        ]);

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
                // Pull down additional information about this structure.
                $structure = $esi->esi->invoke('get', '/universe/structures/{structure_id}/', [
                    'structure_id' => $observer->observer_id,
                ]);
                $refinery->name = $structure->name;
                $refinery->solar_system_id = $structure->solar_system_id;
                $refinery->save();
            }
        }

    }
}
