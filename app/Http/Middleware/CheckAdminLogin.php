<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Whitelist;

class CheckAdminLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If the current logged in user is not admin whitelisted, redirect to the login page.
        if (!Auth::check())
        {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $whitelist = Whitelist::where('eve_id', $user->eve_id)->first();
        if (!isset($whitelist))
        {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
