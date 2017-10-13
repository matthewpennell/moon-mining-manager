<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Seat\Eseye\Cache\NullCache;
use Seat\Eseye\Configuration;
use Seat\Eseye\Containers\EsiConfiguration;
use Seat\Eseye\Containers\EsiAuthentication;
use Seat\Eseye\Eseye;
use App\User;

class CronController extends Controller
{
    
    public function pollRefineries()
    {
        // Set config datasource to Sisi.
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

        $authentication = new EsiAuthentication([
            'secret'        => env('TESTEVEONLINE_CLIENT_SECRET'),
            'client_id'     => env('TESTEVEONLINE_CLIENT_ID'),
            'access_token'  => $new_token->access_token,
            'refresh_token' => $new_token->refresh_token,
        ]);

        $esi = new Eseye();
        $character_info = $esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $user->eve_id,
        ]);
        
        // got data!
        echo $character_info->name;
    }

}
