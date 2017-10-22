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
            'miners' => Miner::all(),
        ]);
        
    }

    /**
     * Show a detailed history of a specific miner.
     */
    public function showMinerDetails($id = NULL)
    {

        // If no current logged in user, show the login page.
        if ($id == NULL)
        {
            return redirect('/');
        }

        // Build an aggregate activity log of all this miner's activities.
        $invoices = Invoice::where('miner_id', $id)->get()->keyBy('created_at')->toArray();
        $mining_activities = MiningActivity::where('miner_id', $id)->get()->keyBy('created_at')->toArray();
        $payments = Payment::where('miner_id', $id)->get()->keyBy('created_at')->toArray();
        $activity_log = array_merge($invoices, $mining_activities, $payments);

        // Sort the log by reverse chronological order.
        krsort($activity_log);

        return view('miners.single', [
            'miner' => Miner::where('eve_id', $id)->first(),
            'activity_log' => $activity_log,
        ]);

    }

}
