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

class PollRefinery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $observer_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->observer_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $esi = new EsiConnection;

        // Retrieve the mining activity log for this refinery.
        $activity_log = $esi->esi->invoke('get', '/corporation/{corporation_id}/mining/observers/{observer_id}/', [
            'corporation_id' => $esi->corporation_id,
            'observer_id' => $this->observer_id,
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
            }
        }
        
    }

}
