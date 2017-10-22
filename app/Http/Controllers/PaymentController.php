<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Miner;
use App\Payment;

class PaymentController extends Controller
{
    
    public function addNewPayment()
    {

        return view('payment/new', [
            'user' => Auth::user(),
            'miners' => Miner::orderBy('name', 'asc')->get(),
        ]);
        
    }

    public function insertNewPayment(Request $request)
    {
        $miner_id = $request->input('miner_id');
        $amount = $request->input('amount');
        if (isset($miner_id) && isset($amount))
        {
            // Create a record of the new payment.
            $payment = new Payment;
            $payment->miner_id = $miner_id;
            $payment->amount_received = $amount;
            $payment->save();
            // Deduct it from the current outstanding balance.
            $miner = Miner::where('eve_id', $miner_id)->first();
            $miner->amount_owed -= $amount;
            $miner->save();
        }
        return redirect('/');
    }

}
