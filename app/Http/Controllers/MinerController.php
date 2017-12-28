<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Miner;
use App\Invoice;
use App\MiningActivity;
use App\Payment;

class MinerController extends Controller
{
    
    /**
     * List all miners together with their total payments.
     */
    public function showMiners()
    {

        return view('miners.all', [
            'miners' => Miner::orderBy('name')->get(),
        ]);
        
    }

    /**
     * Show a detailed history of a specific miner.
     */
    public function showMinerDetails($id = NULL)
    {

        // If no user id supplied, redirect to the miners list.
        if ($id == NULL)
        {
            return redirect('/miners');
        }

        // Retrieve all history of the miner's mining, invoices and payments.
        $mining_activities = MiningActivity::where('miner_id', $id)->get();
        $invoices = Invoice::where('miner_id', $id)->get();
        $payments = Payment::where('miner_id', $id)->get();

        // Loop through each collection and add them to a master array.
        $activity_log = [];
        foreach ($mining_activities as $mining_activity)
        {
            $activity_log[] = $mining_activity;
        }
        foreach ($invoices as $invoice)
        {
            $activity_log[] = $invoice;
        }
        foreach ($payments as $payment)
        {
            $activity_log[] = $payment;
        }

        // Sort the log into reverse chronological order.
        usort($activity_log, [$this, "sortByDate"]);

        return view('miners.single', [
            'miner' => Miner::where('eve_id', $id)->first(),
            'activity_log' => $activity_log,
        ]);

    }

    private function sortByDate($a, $b)
    {
        if ($a->created_at == $b->created_at)
        {
            return 0;
        }
        return ($a->created_at > $b->created_at) ? -1 : 1;
    }

}
