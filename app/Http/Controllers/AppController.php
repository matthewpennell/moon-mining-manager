<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Whitelist;
use App\User;

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

        return view('home', [
            'user' => Auth::user(),
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
