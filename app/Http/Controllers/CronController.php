<?php

namespace App\Http\Controllers;

use App\Http\Controllers\EveController;
use Illuminate\Http\Request;
use App\Refinery;

class CronController extends EveController
{

    /**
     * Cron task to request information on all of the currently active moon mining observer structures.
     */
    public function pollRefineries()
    {
        
        // Retrieve the prime user's character details.
        $character = $this->esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $this->user->eve_id,
        ]);

        // Request a list of all of the active mining observers belonging to the corporation.
        $mining_observers = $this->esi->invoke('get', '/corporation/{corporation_id}/mining/observers/', [
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
                $structure = $this->esi->invoke('get', '/universe/structures/{structure_id}/', [
                    'structure_id' => $observer->observer_id,
                ]);
                $refinery->name = $structure->name;
                $refinery->solar_system_id = $structure->solar_system_id;
            }
            $refinery->is_active = 1;
            $refinery->save();
        }

    }

    /**
     * For each active moon mining observer, we request details of the current mining activity log,
     * parse it, and insert it into the database.
     */
    public function pollMiningObservers()
    {
        
    }

}
