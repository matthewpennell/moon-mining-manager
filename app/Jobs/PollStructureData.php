<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Refinery;
use Illuminate\Support\Facades\Log;

class PollStructureData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;
    private $structure_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->structure_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $esi = new EsiConnection;

        // Pull down additional information about this structure.
        $structure = $esi->esi->invoke('get', '/universe/structures/{structure_id}/', [
            'structure_id' => $this->structure_id,
        ]);

        // Update the refinery item with the new information.
        $refinery = Refinery::where('observer_id', $this->structure_id)->first();
        $refinery->name = $structure->name;
        $refinery->solar_system_id = $structure->solar_system_id;
        $refinery->save();

        Log::info('PollStructureData: updated stored information about refinery ' . $this->structure_id);

    }

}
