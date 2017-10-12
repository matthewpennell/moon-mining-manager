<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Socialite;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Whitelist;

class AuthController extends Controller
{
    /**
     * Redirect the user to the EVE Online SSO page and ask for permission to search corporation assets.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('eveonline-sisi')->scopes(['esi-industry.read_corporation_mining.v1', 'esi-mail.send_mail.v1'])->redirect();
    }

    /**
     * Obtain the user information from EVE Online.
     *
     * @return Response
     */
    public function handleProviderCallback(Request $request)
    {
        $user = Socialite::driver('eveonline-sisi')->user();
        $authUser = $this->findOrCreateUser($user);
        // Check if the user is whitelisted to access the app.
        Whitelist::where('eve_id', $authUser->eve_id)->firstOrFail();
        Auth::login($authUser, true);
        return redirect('/');
    }

    /**
     * Return user if exists; create and return if doesn't
     *
     * @param $user
     * @return User
     */
     private function findOrCreateUser($user)
     {
         if ($authUser = User::where('eve_id', $user->id)->first())
         {
             $authUser->token = $user->token;
             $authUser->refresh_token = $user->refreshToken;
             $authUser->save();
             return $authUser;
         }
 
         return User::create([
             'eve_id' => $user->id,
             'name' => $user->name,
             'avatar' => $user->avatar,
             'token' => $user->token,
             'refresh_token' => $user->refreshToken,
         ]);
     }
    
}