<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Whitelist;
use App\User;
use App\Miner;
use App\Refinery;

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

        // If no current logged in user, show the login page.
        if (!Auth::check())
        {
            return view('login');
        }

        // Calculate the total currently owed and total income generated.
        $total_amount_owed = DB::table('miners')->select(DB::raw('SUM(amount_owed) AS total'))->where('amount_owed', '>', 0)->where('alliance_id', env('EVE_ALLIANCE_ID'))->first();
        $total_income = DB::table('refineries')->select(DB::raw('SUM(income) AS total'))->first();

        return view('home', [
            'user' => Auth::user(),
            'miners' => Miner::where('amount_owed', '>', 0)->where('alliance_id', env('EVE_ALLIANCE_ID'))->get(),
            'ninjas' => Miner::whereNull('alliance_id')->orwhere('alliance_id', '<>', env('EVE_ALLIANCE_ID'))->get(),
            'total_amount_owed' => $total_amount_owed->total,
            'refineries' => Refinery::all(),
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

        // If no current logged in user, show the login page.
        if (!Auth::check())
        {
            return view('login');
        }

        return view('users', [
            'user' => Auth::user(),
            'whitelisted_users' => Whitelist::all(),
        ]);
        
    }

    /**
     * Choosing a new user to whitelist. Displays a list of everyone that has tried
     * to login in the past, with buttons to whitelist.
     */
    public function showUserAccessHistory()
    {

        // If no current logged in user, show the login page.
        if (!Auth::check())
        {
            return view('login');
        }

        return view('add_user', [
            'user' => Auth::user(),
            'access_history' => User::whereNotIn('eve_id', function ($q) {
                $q->select('eve_id')->from('whitelist');
            })->get(),
        ]);
        
    }

    /**
     * Whitelist a new user.
     */
    public function whitelistUser($id = NULL)
    {
        if ($id == NULL)
        {
            return redirect('/access/new');
        }
        $user = Auth::user();
        $whitelist = new Whitelist;
        $whitelist->eve_id = $id;
        $whitelist->added_by = $user->eve_id;
        $whitelist->save();
        return redirect('/access/new');
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

}
