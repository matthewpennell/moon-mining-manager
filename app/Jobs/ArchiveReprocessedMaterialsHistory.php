<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ReprocessedMaterialsHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveReprocessedMaterialsHistory implements ShouldQueue
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
        // Delete any material price history records over 20 days old.
        ReprocessedMaterialsHistory::whereRaw('created_at < ADDDATE(NOW(), INTERVAL -20 DAY)')->delete();
        Log::info('ArchiveReprocessedMaterialsHistory: deleted any material price history records over 20 days old');
    }
}
