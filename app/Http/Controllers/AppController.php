<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Whitelist;
use App\User;
use App\Miner;
use App\Refinery;
use App\Payment;
use App\SolarSystem;
use App\MiningActivity;

class AppController extends Controller
{

    /**
     * App homepage. Check if the user is currently signed in, and either show
     * a signin prompt or the homepage.
     *
     * @return Response
     */
    public function home()
    {

        // Build the WHERE clause to filter by alliance and/or corporation membership.
        $whitelist_where = [];
        $blacklist_where = [];
        if (env('EVE_ALLIANCES_WHITELIST'))
        {
            $whitelist_where[] = 'alliance_id IN (' . env('EVE_ALLIANCES_WHITELIST') . ')';
            $blacklist_where[] = '(alliance_id NOT IN (' . env('EVE_ALLIANCES_WHITELIST') . ') OR alliance_id IS NULL)';
        }
        if (env('EVE_CORPORATIONS_WHITELIST'))
        {
            $whitelist_where[] = 'corporation_id IN (' . env('EVE_CORPORATIONS_WHITELIST') . ')';
            $blacklist_where[] = 'corporation_id NOT IN (' . env('EVE_CORPORATIONS_WHITELIST') . ')';
        }
        if (count($whitelist_where))
        {
            $whitelist_whereRaw = '(' . implode(' OR ', $whitelist_where) . ')';
            $blacklist_whereRaw = '(' . implode(' AND ', $blacklist_where) . ')';
        }

        // Calculate the total currently owed and total income generated.
        $total_amount_owed = DB::table('miners')->select(DB::raw('SUM(amount_owed) AS total'))->where('amount_owed', '>', 0)->whereRaw($whitelist_whereRaw)->first();
        $total_income = DB::table('payments')->select(DB::raw('SUM(amount_received) AS total'))->first();

        // Grab the top miner, refinery and system.
        $top_payer = Payment::select(DB::raw('miner_id, SUM(amount_received) AS total'))->groupBy('miner_id')->orderBy('total', 'desc')->first();
        if (isset($top_payer))
        {
            $top_miner = Miner::where('eve_id', $top_payer->miner_id)->first();
            $top_miner->total = $top_payer->total;
        }
        $top_refinery = Refinery::orderBy('income', 'desc')->first();
        $top_refinery_system = Refinery::select(DB::raw('solar_system_id, SUM(income) AS total'))->groupBy('solar_system_id')->orderBy('total', 'desc')->first();
        if (isset($top_refinery_system))
        {
            $top_system = SolarSystem::find($top_refinery_system->solar_system_id);
            $top_system->total = $top_refinery_system->total;
        }

        return view('home', [
            'top_miner' => (isset($top_miner)) ? $top_miner : null,
            'top_refinery' => (isset($top_refinery)) ? $top_refinery : null,
            'top_system' => (isset($top_system)) ? $top_system : null,
            'miners' => Miner::where('amount_owed', '>=', 1)->whereRaw($whitelist_whereRaw)->orderBy('amount_owed', 'desc')->get(),
            'ninjas' => Miner::whereRaw($blacklist_whereRaw)->get(),
            'total_amount_owed' => $total_amount_owed->total,
            'refineries' => Refinery::orderBy('income', 'desc')->get(),
            'total_income' => $total_income->total,
        ]);

    }

    /**
     * Access management user list. List all the current whitelisted users, together
     * with the person that authorised them.
     * 
     * @return Response
     */
    public function showAuthorisedUsers()
    {

        return view('settings', [
            'admin_users' => Whitelist::where('is_admin', TRUE)->get(),
            'whitelisted_users' => Whitelist::where('is_admin', FALSE)->get(),
            'access_history' => User::whereNotIn('eve_id', function ($q) {
                $q->select('eve_id')->from('whitelist');
            })->get(),
        ]);
        
    }

    /**
     * Whitelist a new user.
     */
    public function makeUserAdmin($id = NULL)
    {
        if ($id == NULL)
        {
            return redirect('/access');
        }
        $user = Auth::user();
        // Check if this user is already in the whitelist table.
        $whitelist = Whitelist::where('eve_id', $id)->first();
        if (!isset($whitelist))
        {
            $whitelist = new Whitelist;
            $whitelist->eve_id = $id;
        }
        $whitelist->is_admin = TRUE;
        $whitelist->added_by = $user->eve_id;
        $whitelist->save();
        return redirect('/access');
    }

    /**
     * Whitelist a new user.
     */
    public function whitelistUser($id = NULL)
    {
        if ($id == NULL)
        {
            return redirect('/access');
        }
        $user = Auth::user();
        $whitelist = new Whitelist;
        $whitelist->eve_id = $id;
        $whitelist->added_by = $user->eve_id;
        $whitelist->save();
        return redirect('/access');
    }

    /**
     * Blacklist a new user. (Well, it's not really a blacklist, just de-whitelist them.)
     */
    public function blacklistUser($id = NULL)
    {
        if ($id == NULL)
        {
            return redirect('/access');
        }
        $user = Whitelist::where('eve_id', $id);
        $user->delete();
        return redirect('/access');
    }

    /**
     * Logout the currently authenticated user.
     *
     * @return Response
     */
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    /**
     * Temporary bugfixing.
     */
    public function bugfix()
    {
        $affected_users = [117894285, 1367849631, 1535275594, 1751372735, 1785602553, 2112014984, 2112276313, 2112318599, 2112354557, 2112815463, 2112822595, 2113174223, 2113201249, 2113218423, 2113223646, 2113276909, 2113313423, 2113433661, 2113730310, 2113804593, 2113818814, 261370742, 556278101, 811475863, 91143710, 91205905, 92421575, 93168526, 93683419, 94616059, 95103814, 95288851, 95393938, 95397408, 95729363, 96277552, 96582223, 96779003, 97005654, 97253903];

        // Loop through each user.
        foreach ($affected_users as $user)
        {
            // Find all of their activity from the 2nd March.
            $activities = MiningActivity::where('miner_id', $user)->where('created_at', '2018-03-02 23:59:59')->get();
            // Loop each activity, and count up the total overcounted tax.
            $overtax = 0;
            foreach ($activities as $activity)
            {
                $overtax += $activity->tax_amount;
            }
            // Deduct the over taxed amount from their current amount owed.
            $miner = Miner::where('eve_id', $user)->first();
            $miner->amount_owed = $miner->amount_owed - $overtax;
            // Save the result.
            $miner->save();
            echo 'Deducted ' . number_format($overtax, 0) . ' ISK over-tax from miner ' . $miner->eve_id . '<br>';
        }
    }

}
