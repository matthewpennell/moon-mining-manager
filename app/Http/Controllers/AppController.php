<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
