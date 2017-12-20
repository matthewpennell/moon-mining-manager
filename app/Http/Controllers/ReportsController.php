<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MiningActivity;
use App\Payment;
use App\Miner;
use Illuminate\Support\Facades\DB;
use App\Jobs\RegenerateInvoices;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    /**
     * Default report view. Show a table of dates with amount mined per day.
     */
    public function main()
    {

        // Grab all mining activity and payments since all time.
        $mining_activity = MiningActivity::all();
        $payment_activity = Payment::all();

        // Find the first and last dates of recorded mining activity.
        $first_date = date('Y-m-d', strtotime(MiningActivity::orderBy('created_at', 'asc')->first()->created_at));
        $last_date = date('Y-m-d', strtotime(MiningActivity::orderBy('created_at', 'desc')->first()->created_at));

        // Make an array of the entire date range.
        $date = $first_date;
        $dates = [date('m-d', strtotime($date))];
        while ($date < $last_date)
        {
            $date = date('Y-m-d', strtotime($date . ' + 1 day'));
            $dates[] = date('m-d', strtotime($date));
        }

        // Loop through all mining activity, and add values to each date.
        $mining = [];
        foreach ($mining_activity as $row)
        {
            $date = date('m-d', strtotime($row->created_at));
            if (isset($mining[$date]))
            {
                $mining[$date] += $row->quantity;
            }
            else
            {
                $mining[$date] = $row->quantity;
            }
        }

        // Loop through all payments, add values to each date in range.
        $payments = [];
        foreach ($payment_activity as $row)
        {
            $date = date('m-d', strtotime($row->created_at));
            if (isset($payments[$date]))
            {
                $payments[$date] += $row->amount_received;
            }
            else
            {
                $payments[$date] = $row->amount_received;
            }
        }

        return view('reports.main', [
            'dates' => $dates,
            'mining' => $mining,
            'payments' => $payments,
        ]);

    }

    /**
     * Manual triggering of the job to regenerate invoices after an error.
     */
    public function fix()
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

        // Figure out the date of the last Monday when invoices should have been generated, and find miners that should have been sent an invoice but weren't.
        $last_monday = date('Y-m-d', strtotime('Monday this week'));
        $miners = Miner::select('eve_id', 'name')->where('amount_owed', '>=', 1)->whereRaw($whitelist_whereRaw)->whereRaw('eve_id NOT IN (SELECT miner_id FROM invoices WHERE DATE(created_at) = "' . $last_monday . '")')->get();
        
        return view('reports.fix', [
            'last_monday' => $last_monday,
            'miners' => $miners,
        ]);
    }

    public function regenerate()
    {
        RegenerateInvoices::dispatch();
        Log::info('ReportsController: dispatched job to regenerate invoices');
        return redirect('/reports');
    }

}
