<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Miner;
use App\Jobs\GenerateInvoice;
use Illuminate\Support\Facades\Log;

class GenerateInvoices implements ShouldQueue
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

        // Build the WHERE clause to filter by alliance and/or corporation membership.
        $whitelist_where = [];
        if (env('EVE_ALLIANCES_WHITELIST'))
        {
            $whitelist_where[] = 'alliance_id IN (' . env('EVE_ALLIANCES_WHITELIST') . ')';
        }
        if (env('EVE_CORPORATIONS_WHITELIST'))
        {
            $whitelist_where[] = 'corporation_id IN (' . env('EVE_CORPORATIONS_WHITELIST') . ')';
        }
        if (count($whitelist_where))
        {
            $whitelist_whereRaw = '(' . implode(' OR ', $whitelist_where) . ')';
        }

        // For all miners in your whitelisted alliances/corporations that currently owe an outstanding balance, queue a job to generate and send an invoice.
        $debtors = Miner::where('amount_owed', '>=', 1000)->whereRaw($whitelist_whereRaw)->get();
        Log::info('GenerateInvoices: found ' . count($debtors) . ' miners with an outstanding balance over 1,000 ISK to be invoiced');
        $delay_counter = 0;

        foreach ($debtors as $miner)
        {
            GenerateInvoice::dispatch($miner->eve_id, $delay_counter * 20);
            Log::info('GenerateInvoices: dispatched job to generate invoice for miner ' . $miner->eve_id . ' and send mail ' . ($delay_counter * 20) . ' seconds later');
            $delay_counter++;
        }

    }

}
