<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Classes\EsiConnection;
use Socialite;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Whitelist;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    private $socialite_driver;

    public function __construct()
    {
        // Set the Socialite driver to use based on whether we are working with TQ or Sisi.
        $this->socialite_driver = (env('ESEYE_DATASOURCE', 'tranquility') != 'singularity') ? 'eveonline' : 'eveonline-sisi';
    }

    /**
     * Redirect the user to the EVE Online SSO page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver($this->socialite_driver)->redirect();
    }

    /**
     * Redirect the user to the EVE Online SSO page and ask for necessary permissions/scopes.
     *
     * @return Response
     */
    public function redirectToProviderForAdmin()
    {
        return Socialite::driver($this->socialite_driver)->scopes([
            'esi-industry.read_corporation_mining.v1',
            'esi-wallet.read_corporation_wallets.v1',
            'esi-mail.send_mail.v1',
            'esi-universe.read_structures.v1',
            'esi-corporations.read_structures.v1',
        ])->redirect();
    }

    /**
     * Obtain the user information from EVE Online.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        
        // Find or create the user.
        $user = Socialite::driver($this->socialite_driver)->user();
        $authUser = $this->findOrCreateUser($user);
        Log::info('AuthController: login attempt by ' . $authUser->name);

        $esi = new EsiConnection;

        // Check if the user is a member of the correct alliance.
        $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $authUser->eve_id,
        ]);

        // If this is a new login, save the corporation ID.
        if (!isset($authUser->corporation_id) || $authUser->corporation_id == 0)
        {
            $authUser->corporation_id = $character->corporation_id;
            $authUser->save();
        }

        $corporation = $esi->esi->invoke('get', '/corporations/{corporation_id}/', [
            'corporation_id' => $character->corporation_id,
        ]);

        // If an alliance is set, it must match the stored environment variable.
        if (isset($corporation->alliance_id) && $corporation->alliance_id && $corporation->alliance_id == env('EVE_ALLIANCE_ID', NULL))
        {
            Auth::login($authUser, true);
            Log::info('AuthController: successful login by ' . $authUser->name);
        }
        else
        {
            Log::info('AuthController: unsuccessful login by ' . $authUser->name . ', alliance match failed');
            return redirect()->route('login');
        }

        // Check if the user is whitelisted to access the administrator area.
        $whitelist = Whitelist::where('eve_id', $authUser->eve_id)->first();
        if (isset($whitelist))
        {
            Log::info('AuthController: successful administrator login by ' . $authUser->name);
            return redirect('/');
        }
        else
        {
            return redirect('/timers');
        }
    }

    /**
     * Return user if exists; create and return if doesn't.
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
             'corporation_id' => 0,
             'name' => $user->name,
             'avatar' => $user->avatar,
             'token' => $user->token,
             'refresh_token' => $user->refreshToken,
         ]);
     }
    
}