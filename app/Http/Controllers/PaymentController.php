<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Miner;
use App\Renter;
use App\Payment;
use App\RentalPayment;
use App\Classes\EsiConnection;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    
    public function addNewPayment()
    {

        // Retrieve all renter records.
        $renters = Renter::all();

        // For all contact character IDs, pull the character information via ESI.
        $esi = new EsiConnection;
        foreach ($renters as $renter)
        {
            $renter->character = $esi->esi->invoke('get', '/characters/{character_id}/', [
                'character_id' => $renter->character_id,
            ]);
        }

        return view('payment.new', [
            'miners' => Miner::orderBy('name', 'asc')->get(),
            'renters' => $renters,
        ]);
        
    }

    public function insertNewPayment(Request $request)
    {

        $miner_id = $request->input('miner_id');
        $rental_id = $request->input('rental_id');
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

            // Log the payment.
            Log::info('PaymentController: payment of ' . number_format($amount) . ' ISK manually submitted for miner ' . $miner_id);

        }

        else if (isset($rental_id) && isset($amount))
        {

            // Grab a reference to the rental record.
            $renter = Renter::find($rental_id);

            // Create a record of the new rental payment.
            $rental_payment = new RentalPayment;
            $rental_payment->renter_id = $renter->character_id;
            $rental_payment->refinery_id = $renter->refinery_id;
            $rental_payment->amount_received = $amount;
            $rental_payment->save();

            // Deduct it from the current outstanding balance.
            $renter->amount_owed -= $amount;
            $renter->save();

            // Log the payment.
            Log::info('PaymentController: rental payment of ' . number_format($amount) . ' ISK manually submitted for renter ' . $renter->character_id . ' renting refinery ' . $renter->refinery_id);

        }

        return redirect('/');

    }

}
