<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\EsiConnection;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $esi = new EsiConnection;

        $result = $esi->esi->setQueryString([
            'categories' => 'character',
            'search' => $request->q,
        ])->invoke('get', '/search/');

        Log::info('SearchController: results returned by /search query', [
            'result' => $result,
        ]);

        // If there are more than ten matching results, we want them to keep typing.
        if (isset($result) && isset($result->character))
        {
            if (count($result->character) > 10)
            {
                return 'Too many matches, keep typing...';
            }
            else
            {
                $matches = [];
                foreach ($result->character as $character_id)
                {
                    $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
                        'character_id' => $character_id,
                    ]);
                    $character->id = $character_id;
                    $portrait = $esi->esi->invoke('get', '/characters/{character_id}/portrait/', [
                        'character_id' => $character_id,
                    ]);
                    $character->portrait = $portrait->px128x128;
                    $corporation = $esi->esi->invoke('get', '/corporations/{corporation_id}/', [
                        'corporation_id' => $character->corporation_id,
                    ]);
                    $character->corporation = $corporation->corporation_name;
                    $matches[] = $character;
                }
                return $matches;
            }

        }
        else
        {
            return 'No matches returned, API may be unreachable...';
        }

    }
}
