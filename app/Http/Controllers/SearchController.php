<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\EsiConnection;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $esi = new EsiConnection;

        $result = $esi->esi->setQueryString([
            'categories' => 'character',
            'search' => $request->q,
        ])->invoke('get', '/search/');

        if (count($result->character) == 1)
        {
            $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
                'character_id' => $result->character[0],
            ]);
            $character->id = $result->character[0];
            $portrait = $esi->esi->invoke('get', '/characters/{character_id}/portrait/', [
                'character_id' => $result->character[0],
            ]);
            $character->portrait = $portrait->px128x128;
            $corporation = $esi->esi->invoke('get', '/corporations/{corporation_id}/', [
                'corporation_id' => $character->corporation_id,
            ]);
            $character->corporation = $corporation->corporation_name;
        }

        if (isset($character))
        {
            return $character;
        }
    }
}
