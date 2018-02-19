<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Renter;
use App\Refinery;
use App\RentalInvoice;
use App\RentalPayment;
use App\Moon;
use App\Classes\EsiConnection;

class RenterController extends Controller
{

    /**
     * List all current renting individuals and corporations.
     */
    public function showRenters()
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

        // Load the renter report.
        return view('renters.home', [
            'renters' => $renters,
        ]);

    }

    /**
     * Show a summary of invoices and payments for a specific refinery.
     */
    public function refineryDetails($id = NULL)
    {

        if ($id == NULL)
        {
            return redirect('/renters');
        }

        $renter = Renter::where('refinery_id', $id)->first();

        // Pull the renter character information via ESI.
        $esi = new EsiConnection;
        $renter->character = $esi->esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $renter->character_id,
        ]);
        $renter->character->avatar = $esi->esi->invoke('get', '/characters/{character_id}/portrait/', [
            'character_id' => $renter->character_id,
        ]);
        $renter->character->corporation = $esi->esi->invoke('get', '/corporations/{corporation_id}/', [
            'corporation_id' => $renter->character->corporation_id,
        ]);

        // Build a list of all the invoice and payment activity of this refinery.
        $invoices = RentalInvoice::where('refinery_id', $id)->get();
        $payments = RentalPayment::where('refinery_id', $id)->get();

        // Loop through each collection and add them to a master array.
        $activity_log = [];
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

        return view('renters.refinery', [
            'renter' => $renter,
            'activity_log' => $activity_log,
        ]);

    }

    /**
     * Show a summary of invoices and payments for a specific character that is renting refinery(s).
     */
    public function renterDetails($id = NULL)
    {

        if ($id == NULL)
        {
            return redirect('/renters');
        }

        // Pull the renter character information via ESI.
        $esi = new EsiConnection;
        $renter = $esi->esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $id,
        ]);
        $renter->avatar = $esi->esi->invoke('get', '/characters/{character_id}/portrait/', [
            'character_id' => $id,
        ]);
        $renter->corporation = $esi->esi->invoke('get', '/corporations/{corporation_id}/', [
            'corporation_id' => $renter->corporation_id,
        ]);

        // Build a list of all the invoice and payment activity of this refinery.
        $invoices = RentalInvoice::where('renter_id', $id)->get();
        $payments = RentalPayment::where('renter_id', $id)->get();

        // Loop through each collection and add them to a master array.
        $activity_log = [];
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

        return view('renters.character', [
            'renter' => $renter,
            'activity_log' => $activity_log,
            'total_rent_paid' => DB::table('rental_payments')->select(DB::raw('SUM(amount_received) AS total'))->where('renter_id', $id)->first()->total,
            'total_rent_due' => DB::table('renters')->select(DB::raw('SUM(amount_owed) AS total'))->where('character_id', $id)->first()->total,
            'rentals' => Renter::where('character_id', $id)->whereNotNull('refinery_id')->get(),
        ]);

    }

    /**
     * Form to edit an existing renter record.
     */
    public function editRenter($id = NULL)
    {
        if ($id == NULL)
        {
            return redirect('/renters');
        }

        // Retrieve more detailed information about the named character.
        $renter = Renter::find($id);
        $esi = new EsiConnection;
        $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $renter->character_id,
        ]);
        $portrait = $esi->esi->invoke('get', '/characters/{character_id}/portrait/', [
            'character_id' => $renter->character_id,
        ]);
        $character->portrait = $portrait->px128x128;
        $corporation = $esi->esi->invoke('get', '/corporations/{corporation_id}/', [
            'corporation_id' => $character->corporation_id,
        ]);
        $character->corporation = $corporation->name;
        $renter->character = $character;

        // Pull all the moon data.
        $moons = Moon::orderBy('region_id')->orderBy('solar_system_id')->orderBy('planet')->orderBy('moon')->get();

        return view('renters.edit', [
            'renter' => $renter,
            'refineries' => Refinery::all(),
            'moons' => $moons,
        ]);
    }

    /**
     * Form to create a new renter record.
     */
    public function addNewRenter()
    {
        // Pull all the moon data.
        $moons = Moon::orderBy('region_id')->orderBy('solar_system_id')->orderBy('planet')->orderBy('moon')->get();

        return view('renters.new', [
            'refineries' => Refinery::all(),
            'moons' => $moons,
        ]);
    }

    /**
     * Handle new renter form submission.
     */
    public function saveNewRenter(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required',
            'character_id' => 'required|numeric',
            'refinery_id' => 'nullable|numeric',
            'moon_id' => 'nullable|numeric',
            'monthly_rental_fee' => 'required|numeric',
            'start_date' => 'required|date',
        ]);

        // If validation rules pass, then create the new Renter object.
        $renter = new Renter;
        $renter->type = $request->type;
        $renter->character_id = $request->character_id;
        $renter->refinery_id = $request->refinery_id;
        $renter->moon_id = $request->moon_id;
        $renter->notes = $request->notes;
        $renter->monthly_rental_fee = $request->monthly_rental_fee;
        $renter->start_date = $request->start_date;
        $renter->save();

        return redirect('/renters');
    }

    /**
     * Save updated information on an existing renter.
     */
    public function updateRenter($id = NULL, Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required',
            'character_id' => 'required|numeric',
            'refinery_id' => 'nullable|numeric',
            'moon_id' => 'nullable|numeric',
            'monthly_rental_fee' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        // If validation rules pass, then update the existing Renter record.
        $renter = Renter::find($id);
        $renter->type = $request->type;
        $renter->character_id = $request->character_id;
        $renter->refinery_id = $request->refinery_id;
        $renter->moon_id = $request->moon_id;
        $renter->notes = $request->notes;
        $renter->monthly_rental_fee = $request->monthly_rental_fee;
        $renter->start_date = $request->start_date;
        $renter->end_date = $request->end_date;
        $renter->save();

        return redirect('/renters');
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
