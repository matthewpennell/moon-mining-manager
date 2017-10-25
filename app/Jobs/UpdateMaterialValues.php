<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ReprocessedMaterial;
use App\Jobs\UpdateMaterialValue;
use App\Jobs\UpdateOreValues;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateMaterialValues implements ShouldQueue
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
        // Retrieve the list of all materials that can be found in moon ores.
        $materials = ReprocessedMaterial::all();
        $delay_counter = 0;

        // Loop through and create a job to poll the API for price history.
        foreach ($materials as $material)
        {
            UpdateMaterialValue::dispatch($material->materialTypeID)->delay(Carbon::now()->addSeconds($delay_counter * 5));
            $delay_counter++;
        }

        // After those jobs have all run, recalculate all ore values.
        UpdateOreValues::dispatch()->delay(Carbon::now()->addSeconds($delay_counter * 5));
        Log::info('UpdateMaterialValues: created jobs to update all reprocessed material entries');
    }
}
