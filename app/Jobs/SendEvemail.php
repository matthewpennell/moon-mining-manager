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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEvemail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
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
        Log::info('SendEvemail: sent evemail to character ' . $this->mail['recipients'][0]['recipient_id']);
    }

    /**
     * Handle failure of sending a mail.
     */
    public function failed(Exception $exception)
    {
        // Check what type of exception was thrown.
        if (
            (
                is_object($exception->getEsiResponse()) && (
                    stristr($exception->getEsiResponse()->error, 'Too many errors') || 
                    stristr($exception->getEsiResponse()->error, 'This software has exceeded the error limit for ESI')
                )
            ) || (
                is_string($exception->getEsiResponse()) && (
                    stristr($exception->getEsiResponse(), 'Too many errors') || 
                    stristr($exception->getEsiResponse(), 'This software has exceeded the error limit for ESI')
                )
            )
        ) {
            // We somehow have triggered the error rate limiter, stop requeueing jobs until we can figure out what broke. :(
            Log::info('SendEvemail: bounceback due to hitting the error rate limiter, dumping email job');
            mail(
                env('ADMIN_EMAIL'), 
                'Mining Manager rate limiter alert', 
                date('Y-m-d H:i:s') . ' - SendEvemail: bounceback due to hitting the error rate limiter, dumping email job',
                'From: ' . env('MAIL_FROM_NAME') . ' <' . env('MAIL_FROM_ADDRESS') . '>'
            );
        }
        elseif (stristr($exception->getEsiResponse()->error, 'ContactCostNotApproved'))
        {
            // We want to ignore CSPA charge related errors, since they will never send successfully.
            Log::info('SendEvemail: bounceback due to ContactCostNotApproved, dumping email job');
        }
        elseif (stristr($exception->getEsiResponse()->error, 'MailStopSpamming'))
        {
            // If we triggered the anti-spam rate limiter, we want to try again in a few hours.
            SendEvemail::dispatch($this->mail)->delay(Carbon::now()->addHours(3));
            Log::info('SendEvemail: bounceback due to MailStopSpamming, re-queued job to send mail in 3 hours');
        }
        else
        {
            // Send failed for some other reason, try again in a while.
            SendEvemail::dispatch($this->mail)->delay(Carbon::now()->addMinutes(15));
            Log::info('SendEvemail: re-queued job to send mail in 15 minutes');
        }
    }

}
