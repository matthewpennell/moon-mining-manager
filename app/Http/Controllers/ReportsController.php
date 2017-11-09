<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MiningActivity;
use App\Payment;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Default report view. Show a table of dates with amount mined per day.
     */
    public function main()
    {

        $daily_mining = MiningActivity::select(DB::raw('SUM(quantity) AS quantity, DAY(updated_at) AS order_day, MONTH(updated_at) AS order_month, YEAR(updated_at) AS order_year'))
            ->groupBy('order_day', 'order_month', 'order_year')
            ->orderBy('order_year', 'desc')
            ->orderBy('order_month', 'desc')
            ->orderBy('order_day', 'desc')
            ->get();

        $daily_income = Payment::select(DB::raw('SUM(amount_received) AS amount, DAY(updated_at) AS order_day, MONTH(updated_at) AS order_month, YEAR(updated_at) AS order_year'))
            ->groupBy('order_day', 'order_month', 'order_year')
            ->orderBy('order_year', 'desc')
            ->orderBy('order_month', 'desc')
            ->orderBy('order_day', 'desc')
            ->get();

        return view('reports.main', [
            'daily_mining' => $daily_mining,
            'daily_income' => $daily_income,
        ]);

    }

}
