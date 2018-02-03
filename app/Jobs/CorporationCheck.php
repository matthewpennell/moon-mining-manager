<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Miner;
use App\Corporation;
use App\Alliance;
use Illuminate\Support\Facades\Log;

class CorporationCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;
    private $miner_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->miner_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $esi = new EsiConnection;

        // Check if the miner already exists.
        $miner = Miner::where('eve_id', $this->miner_id)->first();
        Log::info('CorporationCheck: checking miner ' . $this->miner_id);
        $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $this->miner_id,
        ]);

        // Check if they are still in the same corporation as last time we checked.
        if ($miner->corporation_id == $character->corporation_id)
        {
            Log::info('CorporationCheck: miner ' . $this->miner_id . ' is still in the same corporation ' . $character->corporation_id);
        }
        else
        {

            // Update the miner's stored corporation ID.
            $miner->corporation_id = $character->corporation_id;
            Log::info('CorporationCheck: miner ' . $this->miner_id . ' has moved to corporation ' . $character->corporation_id);

            // Check if they have moved to another corporation we know about already.
            $existing_corporation = Corporation::where('corporation_id', $character->corporation_id)->first();
            if (!isset($existing_corporation))
            {

                // This is a new corporation, retrieve all of the relevant details.
                $corporation = $esi->esi->invoke('get', '/corporations/{corporation_id}/', [
                    'corporation_id' => $character->corporation_id,
                ]);
                $new_corporation = new Corporation;
                $new_corporation->corporation_id = $character->corporation_id;
                $new_corporation->name = $corporation->name;
                $new_corporation->save();
                Log::info('CorporationCheck: stored new corporation ' . $corporation->name);

                // Check if their new corporation is a different alliance.
                if (isset($corporation->alliance_id))
                {
                    $miner->alliance_id = $corporation->alliance_id;
                    $existing_alliance = Alliance::where('alliance_id', $corporation->alliance_id)->first();
                    if (!isset($existing_alliance))
                    {
                        // This is a new alliance, save the details.
                        $new_alliance = new Alliance;
                        $new_alliance->alliance_id = $corporation->alliance_id;
                        $alliance = $esi->esi->invoke('get', '/alliances/{alliance_id}/', [
                            'alliance_id' => $corporation->alliance_id,
                        ]);
                        $new_alliance->name = $alliance->name;
                        $new_alliance->save();
                        Log::info('CorporationCheck: stored new alliance ' . $alliance->name);
                    }
                }
    
            }

            // Save the updated miner record.
            $miner->save();

        }

    }

}
