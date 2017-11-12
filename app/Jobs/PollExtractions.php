<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Refinery;
use Illuminate\Support\Facades\Log;

class PollExtractions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $esi = new EsiConnection;
        
        Log::info('PollExtractions: clearing all extraction data more than 2 days old');

        // Delete any extraction data that relates to periods that have already passed (the field natural decay time + 2 days).
        $cutoff = date('Y-m-d H:m:s', time() - (2 * 24 * 60 * 60));
        Refinery::where('natural_decay_time', '<', $cutoff)->update([
            'extraction_start_time' => NULL,
            'chunk_arrival_time' => NULL,
            'natural_decay_time' => NULL,
        ]);

        // Request all active extraction cycle information for the prime user's corporation.
        $structures = $esi->esi->invoke('get', '/corporation/{corporation_id}/mining/extractions/', [
            'corporation_id' => $esi->corporation_id,
        ]);

        // Loop through all the extraction data, updating the current status and time remaining for any active extraction cycles.
        foreach ($structures as $structure)
        {
            $refinery = Refinery::where('observer_id', $structure->structure_id)->first();
            $refinery->extraction_start_time = $this->convertTimestampFormat($structure->extraction_start_time);
            $refinery->chunk_arrival_time = $this->convertTimestampFormat($structure->chunk_arrival_time);
            $refinery->natural_decay_time = $this->convertTimestampFormat($structure->natural_decay_time);
            $refinery->save();
            Log::info('PollExtractions: saved current extraction timestamps for ' . $structure->structure_id);
        }

    }

    /**
     * Convert from ISO 8601 timestamp format to MySQL TIMESTAMP format.
     */
    private function convertTimestampFormat($timestamp)
    {
        return str_replace('T', ' ', substr($timestamp, 0, 19));
    }

}
