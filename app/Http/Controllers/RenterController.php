<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Renter;
use App\Refinery;
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
     * Form to edit an existing renter record.
     */
    public function editRenter($id = NULL)
    {
        if ($id == NULL)
        {
            return redirect('/renters');
        }
        return view('renters.edit', [
            'renter' => Renter::find($id),
            'refineries' => Refinery::all(),
        ]);
    }

    /**
     * Form to create a new renter record.
     */
    public function addNewRenter()
    {
        return view('renters.new', [
            'refineries' => Refinery::all(),
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
            'monthly_rental_fee' => 'required|numeric',
            'start_date' => 'required|date',
        ]);

        // If validation rules pass, then create the new Renter object.
        $renter = new Renter;
        $renter->type = $request->type;
        $renter->character_id = $request->character_id;
        $renter->refinery_id = $request->refinery_id;
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
            'monthly_rental_fee' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        // If validation rules pass, then update the existing Renter record.
        $renter = Renter::find($id);
        $renter->type = $request->type;
        $renter->character_id = $request->character_id;
        $renter->refinery_id = $request->refinery_id;
        $renter->notes = $request->notes;
        $renter->monthly_rental_fee = $request->monthly_rental_fee;
        $renter->start_date = $request->start_date;
        $renter->save();

        return redirect('/renters');
    }

}
