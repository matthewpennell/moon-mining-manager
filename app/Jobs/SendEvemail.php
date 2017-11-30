<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendEvemail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    private $mail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mail)
    {
        $this->mail = $mail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $esi = new EsiConnection;
        $esi->esi->setBody($this->mail);
        $esi->esi->invoke('post', '/characters/{character_id}/mail/', [
            'character_id' => $esi->character_id,
        ]);
        Log::info('SendEvemail: sent evemail');
    }

    /**
     * Handle failure of sending a mail.
     */
    public function failed(Exception $exception)
    {
        SendEvemail::dispatch($this->mail)->delay(Carbon::now()->addMinutes(10));
        Log::info('SendEvemail: re-queued job to send mail in 10 minutes');
    }

}
