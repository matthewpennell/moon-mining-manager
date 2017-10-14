<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Seat\Eseye\Configuration;
use Seat\Eseye\Containers\EsiAuthentication;
use Seat\Eseye\Eseye;
use App\User;
use App\Refinery;

class CronController extends Controller
{
    
    /**
     * Cron task to request information on all of the currently active moon mining observer structures.
     */
    public function pollRefineries()
    {
        // Set config datasource using environment variable.
        $configuration = Configuration::getInstance();
        $configuration->datasource = env('ESEYE_DATASOURCE', 'tranquility');

        // Create authentication with app details and refresh token from user 1.
        $user = User::first();

        // Need to request a new valid access token from EVE SSO using the refresh token of the original request.
        $url = 'https://sisilogin.testeveonline.com/oauth/token';
        $response = Curl::to($url)
            ->withData(array(
                'grant_type' => "refresh_token",
                'refresh_token' => $user->refresh_token
            ))
            ->withHeaders(array(
                'Authorization: Basic ' . base64_encode(env('TESTEVEONLINE_CLIENT_ID') . ':' . env('TESTEVEONLINE_CLIENT_SECRET'))
            ))
            ->enableDebug('logFile.txt')
            ->post();
        $new_token = json_decode($response);
        $user->refresh_token = $new_token->refresh_token;
        $user->save();

        $authentication = new EsiAuthentication([
            'secret'        => env('TESTEVEONLINE_CLIENT_SECRET'),
            'client_id'     => env('TESTEVEONLINE_CLIENT_ID'),
            'access_token'  => $new_token->access_token,
            'refresh_token' => $user->refresh_token,
            'scopes'        => [
                                'esi-industry.read_corporation_mining.v1',
                                'esi-wallet.read_corporation_wallet.v1',
                                'esi-mail.send_mail.v1',
                                'esi-universe.read_structures.v1',
                            ],
            'token_expires' => date('Y-m-d H:i:s', time() + $new_token->expires_in),
        ]);

        // Create ESI API object.
        $esi = new Eseye($authentication);
        
        // Retrieve the user's character details.
        $character = $esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $user->eve_id,
        ]);

        // Request a list of all of the active mining observers belonging to the corporation.
        $mining_observers = $esi->invoke('get', '/corporation/{corporation_id}/mining/observers/', [
            'corporation_id' => $character->corporation_id,
        ]);
        
        // Process the refineries list. For each entry, we want to check and see if it already exists 
        // in the database. If it does, we flag that it is currently active. If it doesn't, we create 
        // a new database entry for it.
        foreach ($mining_observers as $observer)
        {
            $refinery = Refinery::find($observer->observer_id);
            if ($refinery->isEmpty())
            {
                $refinery = new Refinery;
                $refinery->observer_id = $observer->observer_id;
                $refinery->observer_type = $observer->observer_type;
                // Pull down additional information about this structure.
                $structure = $esi->invoke('get', '/universe/structures/{structure_id}/', [
                    'structure_id' => $observer->observer_id,
                ]);
                $refinery->name = $structure->name;
                $refinery->solar_system_id = $structure->solar_system_id;
            }
            $refinery->is_active = 1;
            $refinery->save();
        }

    }

}
