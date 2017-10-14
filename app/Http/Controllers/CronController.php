<?php

namespace App\Http\Controllers;

use App\Http\Controllers\EveController;
use Illuminate\Http\Request;
use App\Refinery;
use App\MiningActivity;
use App\Miner;

class CronController extends EveController
{

    /**
     * Cron task to request information on all of the currently active moon mining observer structures.
     */
    public function pollRefineries()
    {
        
        // Request a list of all of the active mining observers belonging to the corporation.
        $mining_observers = $this->esi->invoke('get', '/corporation/{corporation_id}/mining/observers/', [
            'corporation_id' => $this->corporation_id,
        ]);
        
        // Process the refineries list. For each entry, we want to check and see if it already exists 
        // in the database. If it doesn't, we create a new database entry for it.
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
            $refinery->save();
        }

    }

    /**
     * For each moon mining observer, we request details of the current mining activity log,
     * parse it, and insert it into the database. Any unrecognised miners are also added.
     */
    public function pollMiningObservers()
    {
        
        // Grab all of the refineries and loop through them.
        $refineries = Refinery::all();

        foreach ($refineries as $refinery)
        {
            // Retrieve the mining activity log for this refinery.
            $activity_log = $this->esi->invoke('get', '/corporation/{corporation_id}/mining/observers/{observer_id}/', [
                'corporation_id' => $this->corporation_id,
                'observer_id' => $refinery->observer_id,
            ]);
            foreach ($activity_log as $log_entry)
            {
                // Check whether this entry has already been recorded.
                $existing_activity = MiningActivity::where([
                    'miner_id' => $log_entry->character_id,
                    'refinery_id' => $refinery->observer_id,
                    'type_id' => $log_entry->type_id,
                    'quantity' => $log_entry->quantity,
                ])->get();
                if ($existing_activity->isEmpty())
                {
                    // Create a new entry in the database for this activity.
                    $mining_activity = new MiningActivity;
                    $mining_activity->miner_id = $log_entry->character_id;
                    $mining_activity->refinery_id = $refinery->observer_id;
                    $mining_activity->type_id = $log_entry->type_id;
                    $mining_activity->quantity = $log_entry->quantity;
                    $mining_activity->save();
                }
                // Check if this miner is already known.
                $existing_miner = Miner::where('eve_id', $log_entry->character_id)->get();
                if ($existing_miner->isEmpty())
                {
                    // Create a new entry for this miner, including pulling additional information.
                    $miner = new Miner;
                    $miner->eve_id = $log_entry->character_id;
                    $character = $this->esi->invoke('get', '/characters/{character_id}/', [
                        'character_id' => $log_entry->character_id,
                    ]);
                    $miner->name = $character->name;
                    $miner->corporation_id = $character->corporation_id;
                    $portrait = $this->esi->invoke('get', '/characters/{character_id}/portrait/', [
                        'character_id' => $log_entry->character_id,
                    ]);
                    $miner->avatar = $portrait->px128x128;
                    $miner->save();
                }
            }
        }

    }

    /**
     * 
     */
    public function pollWallet()
    {

    }

    /**
     * Calculate the total amount owing per person, generate invoices, send emails.
     */
    public function generateInvoices()
    {
        
    }

}
