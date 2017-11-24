<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Refinery;

class TimerController extends Controller
{

    /**
     * List all upcoming detonations.
     */
    public function home()
    {

        return view('timers', [
            'is_admin_corporation_member' => (Auth::user()->corporation_id == env('EVE_ADMIN_CORPORATION', NULL)) ? TRUE : FALSE,
            'timers' => Refinery::where('name', 'LIKE', '%BRAVE%')->whereNotNull('extraction_start_time')->orderBy('chunk_arrival_time')->get(),
        ]);

    }

    /**
     * Allow corporation admins to claim responsibility for detonations.
     */
    public function claim($claim = 1, $refinery = NULL)
    {

        // If no refinery provided or the user is not authorised to perform this action, return to the list.
        if ($refinery == NULL || Auth::user()->corporation_id != env('EVE_ADMIN_CORPORATION', NULL))
        {
            return redirect('/timers');
        }

        $refinery = Refinery::where('observer_id', $refinery)->firstOrFail();
        if ($claim == 2)
        {
            $refinery->claimed_by_secondary = Auth::user()->eve_id;
        }
        else
        {
            $refinery->claimed_by_primary = Auth::user()->eve_id;
        }
        $refinery->save();
        return redirect('/timers');

    }

    /**
     * Clear admin claims for detonations.
     */
    public function clear($claim = 1, $refinery = NULL)
    {

        // If no refinery provided or the user is not authorised to perform this action, return to the list.
        if ($refinery == NULL || Auth::user()->corporation_id != env('EVE_ADMIN_CORPORATION', NULL))
        {
            return redirect('/timers');
        }

        $refinery = Refinery::where('observer_id', $refinery)->firstOrFail();
        if ($claim == 2)
        {
            $refinery->claimed_by_secondary = NULL;
        }
        else
        {
            $refinery->claimed_by_primary = NULL;
        }
        $refinery->save();
        return redirect('/timers');

    }

}
