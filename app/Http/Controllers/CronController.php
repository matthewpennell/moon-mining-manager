<?php

namespace App\Http\Controllers;

use App\Jobs\PollRefineries;
use App\Jobs\PollMiningObservers;
use App\Jobs\PollWallet;
use App\Jobs\GenerateInvoices;

use App\Classes\EsiConnection;

class CronController extends Controller
{

    /**
     * Cron task to request information on all of the currently active moon mining observer structures.
     */
    public function pollRefineries()
    {
        PollRefineries::dispatch();
        echo 'Next, <a href="/cron/observers">observers</a>';
    }

    /**
     * For each moon mining observer, we request details of the current mining activity log,
     * parse it, and insert it into the database. Any unrecognised miners are also added.
     */
    public function pollMiningObservers()
    {
        PollMiningObservers::dispatch();
        echo 'Next, <a href="/cron/invoices">invoices</a>';
    }

    /**
     * 
     */
    public function pollWallet()
    {
        //PollWallet::dispatch();
        $esi = new EsiConnection;

        // Request the corp wallet info.
        $wallet = $esi->esi->invoke('get', '/corporations/{corporation_id}/wallets/', [
            'corporation_id' => $esi->corporation_id,
        ]);

        foreach ($wallet as $division)
        {
            echo 'Division ' . $division->division . ' : ' . $division->balance . ' ISK<br>';
        }

        // Request the transactions from a specific division.
        $transactions = $esi->esi->invoke('get', '/corporations/{corporation_id}/wallets/{division}/transactions/', [
            'corporation_id' => $esi->corporation_id,
            'division' => 1,
        ]);

        foreach ($transactions as $transaction)
        {
            echo '<pre style="border: 1px dashed; margin: 10px;">';
            print_r($transaction);
            echo '</pre>';
        }

        echo '<a href="/cron/refineries">Back to the start?</a>';
    }

    /**
     * Calculate the total amount owing per person, generate invoices, send emails.
     */
    public function generateInvoices()
    {
        GenerateInvoices::dispatch();
        echo 'Next, <a href="/cron/wallet">wallet</a>';
    }

}
