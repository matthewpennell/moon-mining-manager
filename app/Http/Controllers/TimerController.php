<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Refinery;
use App\Miner;
use App\Invoice;
use App\MiningActivity;
use App\Payment;
use App\Whitelist;
use Illuminate\Support\Facades\Log;

class TimerController extends Controller
{

    /**
     * List all upcoming detonations.
     */
    public function home()
    {

        // Grab a reference to the currently logged-in user.
        $user = Auth::user();

        // Find their Miner record, if it exists.
        $miner = Miner::where('eve_id', $user->eve_id)->first();

        if (isset($miner))
        {
            // Build an aggregate activity log of all this miner's activities.
            $invoices = Invoice::where('miner_id', $miner->eve_id)->get()->keyBy('created_at')->toArray();
            $mining_activities = MiningActivity::where('miner_id', $miner->eve_id)->get()->keyBy('created_at')->toArray();
            $payments = Payment::where('miner_id', $miner->eve_id)->get()->keyBy('created_at')->toArray();
            $activity_log = array_merge($invoices, $mining_activities, $payments);
            // Sort the log by reverse chronological order.
            krsort($activity_log);
        }

        // Retrieve the current user's whitelisted status.
        $whitelist = Whitelist::where('eve_id', $user->eve_id)->first();

        // Retrieve all refineries with active extraction periods.
        $refineries = Refinery::where('name', 'LIKE', '%BRAVE%')->whereNotNull('extraction_start_time')->orderBy('chunk_arrival_time')->get();

        // Parse the anticipated detonation time, based on any managed detonations.
        foreach ($refineries as $refinery)
        {
            // Set the original expected detonation time.
            if (isset($refinery->claimed_by_primary) || isset($refinery->claimed_by_secondary))
            {
                $refinery->detonation_time = $refinery->chunk_arrival_time;
            }
            else
            {
                $refinery->detonation_time = $refinery->natural_decay_time;
            }
            if ($refinery->custom_detonation_time != NULL)
            {
                // Found a custom detonation time, replace the original time with the new time.
                $date = date('Y-m-d', strtotime($refinery->detonation_time));
                $refinery->detonation_time = $date . ' ' . $refinery->custom_detonation_time;
                // Check we haven't skipped over a day boundary.
                if ($refinery->detonation_time > $refinery->natural_decay_time)
                {
                    $refinery->detonation_time = date('Y-m-d H:i:s', strtotime($refinery->detonation_time . ' -1 days'));
                }
                elseif ($refinery->detonation_time < $refinery->chunk_arrival_time)
                {
                    $refinery->detonation_time = date('Y-m-d H:i:s', strtotime($refinery->detonation_time . ' +1 days'));
                }
            }
        }

        return view('timers', [
            'miner' => (isset($miner)) ? $miner : NULL,
            'activity_log' => (isset($activity_log)) ? $activity_log : NULL,
            'is_whitelisted_user' => (isset($whitelist)) ? TRUE : FALSE,
            'timers' => $refineries,
        ]);

    }

    /**
     * Allow corporation admins to claim responsibility for detonations.
     */
    public function claim(Request $request, $claim = 1, $refinery = NULL)
    {

        // Retrieve the current user's whitelisted status.
        $whitelist = Whitelist::where('eve_id', Auth::user()->eve_id)->first();

        // If no refinery provided or the user is not authorised to perform this action, return to the list.
        if ($refinery == NULL || !isset($whitelist))
        {
            return redirect('/timers');
        }

        $refinery = Refinery::where('observer_id', $refinery)->firstOrFail();
        if ($claim == 2)
        {
            $refinery->claimed_by_secondary = Auth::user()->eve_id;
            if (isset($request->detonation) && $refinery->custom_detonation_time == NULL)
            {
                $refinery->custom_detonation_time = $request->detonation;
            }
        }
        else
        {
            $refinery->claimed_by_primary = Auth::user()->eve_id;
            if (isset($request->detonation))
            {
                $refinery->custom_detonation_time = $request->detonation;
            }
        }
        $refinery->save();
        Log::info('TimerController: detonation for refinery ' . $refinery->observer_id . ' claimed by ' . Auth::user()->name);
        return redirect('/timers');

    }

    /**
     * Clear admin claims for detonations.
     */
    public function clear($claim = 1, $refinery = NULL)
    {

        // Retrieve the current user's whitelisted status.
        $whitelist = Whitelist::where('eve_id', Auth::user()->eve_id)->first();

        // If no refinery provided or the user is not authorised to perform this action, return to the list.
        if ($refinery == NULL || !isset($whitelist))
        {
            return redirect('/timers');
        }

        $refinery = Refinery::where('observer_id', $refinery)->firstOrFail();
        if ($claim == 2)
        {
            $refinery->claimed_by_secondary = NULL;
            if ($refinery->claimed_by_primary == NULL)
            {
                $refinery->custom_detonation_time = NULL;
            }
        }
        else
        {
            $refinery->claimed_by_primary = NULL;
            $refinery->custom_detonation_time = NULL;
        }
        $refinery->save();
        Log::info('TimerController: detonation for refinery ' . $refinery->observer_id . ' no longer claimed by ' . Auth::user()->name);
        return redirect('/timers');

    }

}
