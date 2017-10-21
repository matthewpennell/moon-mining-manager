<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Refinery;
use App\MiningActivity;
use App\Miner;

class PollMiningObservers implements ShouldQueue
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

        // Grab all of the refineries and loop through them.
        $refineries = Refinery::all();
        
        foreach ($refineries as $refinery)
        {
            // Retrieve the mining activity log for this refinery.
            $activity_log = $esi->esi->invoke('get', '/corporation/{corporation_id}/mining/observers/{observer_id}/', [
                'corporation_id' => $esi->corporation_id,
                'observer_id' => $refinery->observer_id,
            ]);
            foreach ($activity_log as $log_entry)
            {
                // Check whether this entry has already been recorded.
                $existing_activity = MiningActivity::where([
                    'miner_id' => $log_entry->character_id,
                    'refinery_id' => $refinery->observer_id,
                    'type_id' => $log_entry->type_id,
                    'quantity' => $log_entry->quantity,
                ])->first();
                if (!isset($existing_activity))
                {
                    // Create a new entry in the database for this activity.
                    $mining_activity = new MiningActivity;
                    $mining_activity->miner_id = $log_entry->character_id;
                    $mining_activity->refinery_id = $refinery->observer_id;
                    $mining_activity->type_id = $log_entry->type_id;
                    $mining_activity->quantity = $log_entry->quantity;
                    $mining_activity->save();
                    // Check if this miner is already known.
                    $existing_miner = Miner::where('eve_id', $log_entry->character_id)->first();
                    if (!isset($existing_miner))
                    {
                        // Create a new entry for this miner, including pulling additional information.
                        $miner = new Miner;
                        $miner->eve_id = $log_entry->character_id;
                        $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
                            'character_id' => $log_entry->character_id,
                        ]);
                        $miner->name = $character->name;
                        $miner->corporation_id = $character->corporation_id;
                        $portrait = $esi->esi->invoke('get', '/characters/{character_id}/portrait/', [
                            'character_id' => $log_entry->character_id,
                        ]);
                        $miner->avatar = $portrait->px128x128;
                        $miner->save();
                    }
                }
            }
        }
    }

}
