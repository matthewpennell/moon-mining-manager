<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Miner;

class MinerCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $existing_miner = Miner::where('eve_id', $this->miner_id)->first();

        // If not, create a new entry, including pulling additional information.
        if (!isset($existing_miner))
        {
            $miner = new Miner;
            $miner->eve_id = $this->miner_id;
            $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
                'character_id' => $this->miner_id,
            ]);
            $miner->name = $character->name;
            $miner->corporation_id = $character->corporation_id;
            $portrait = $esi->esi->invoke('get', '/characters/{character_id}/portrait/', [
                'character_id' => $this->miner_id,
            ]);
            $miner->avatar = $portrait->px128x128;
            $alliance = $esi->esi->invoke('get', '/corporations/{corporation_id}/', [
                'corporation_id' => $character->corporation_id,
            ]);
            $miner->alliance_id = $alliance->alliance_id;
            $miner->save();
        }

    }

}
