<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Renter;
use App\Jobs\GenerateRentalInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateRentalInvoices implements ShouldQueue
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

        // Grab all the renters with active agreements.
        $renters = Renter::whereRaw('refinery_id IS NOT NULL && start_date <= CURDATE() AND (end_date IS NULL OR end_date >= CURDATE())')->get();

        // Loop through all the renters and send an invoice for the appropriate amount (taking into account partial months).
        $delay_counter = 1;
        foreach ($renters as $renter)
        {
            // Queue jobs to create and send the individual invoices.
            GenerateRentalInvoice::dispatch($renter->id, $delay_counter)->delay(Carbon::now()->addSeconds($delay_counter * 10));
            Log::info('GenerateRentalInvoices: dispatched job to generate invoice for renter ' . $renter->character_id . ' and send mail in ' . $delay_counter . ' minutes');
            $delay_counter++;
        }

    }
}
