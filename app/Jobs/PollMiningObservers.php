<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Refinery;
use App\Jobs\PollRefinery;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PollMiningObservers implements ShouldQueue
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

        // Grab all of the refineries and loop through them.
        $refineries = Refinery::all();
        $delay_counter = 0;

        Log::info('PollMiningObservers: creating jobs to poll ' . count($refineries) . ' refineries');
        
        // For each refinery create a new job in the queue to poll the API.
        foreach ($refineries as $refinery)
        {
            PollRefinery::dispatch($refinery->observer_id)->delay(Carbon::now()->addMinutes($delay_counter));
            $delay_counter++;
        }

    }

}
