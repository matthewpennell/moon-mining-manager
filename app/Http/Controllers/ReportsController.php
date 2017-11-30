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

        $daily_mining = MiningActivity::select(DB::raw('SUM(quantity) AS quantity, DAY(created_at) AS order_day, MONTH(created_at) AS order_month, YEAR(created_at) AS order_year'))
            ->groupBy('order_day', 'order_month', 'order_year')
            ->orderBy('order_year', 'asc')
            ->orderBy('order_month', 'asc')
            ->orderBy('order_day', 'asc')
            ->get();

        $daily_income = Payment::select(DB::raw('SUM(amount_received) AS amount, DAY(updated_at) AS order_day, MONTH(updated_at) AS order_month, YEAR(updated_at) AS order_year'))
            ->groupBy('order_day', 'order_month', 'order_year')
            ->orderBy('order_year', 'asc')
            ->orderBy('order_month', 'asc')
            ->orderBy('order_day', 'asc')
            ->get();

        return view('reports.main', [
            'daily_mining' => $daily_mining,
            'daily_income' => $daily_income,
        ]);

    }

}
