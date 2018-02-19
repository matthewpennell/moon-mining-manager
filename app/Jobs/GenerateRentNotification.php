<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Jobs\SendEvemail;
use App\Template;
use App\Refinery;
use App\Renter;
use App\RentalInvoice;
use Carbon\Carbon;
use App\Classes\EsiConnection;
use Illuminate\Support\Facades\Log;

class GenerateRentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;
    private $id;
    private $mail_delay;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $mail_delay = 20)
    {
        $this->id = $id;
        $this->mail_delay = $mail_delay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Retrieve the renter record.
        $renter = Renter::find($this->id);
        
        // Request the character name for this rental agreement.
        $esi = new EsiConnection;
        $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $renter->character_id,
        ]);

        // Grab a reference to the refinery that is being rented.
        $refinery = Refinery::where('observer_id', $renter->refinery_id)->first();

        // Round the rental amount since we don't need to worry about cents.
        $monthly_rental_fee = round($renter->monthly_rental_fee);

        // Pick up the renter notice template to apply text substitutions.
        $template = Template::where('name', 'renter_notification')->first();
        
        // Grab the template subject and body.
        $subject = $template->subject;
        $body = $template->body;
        
        // Replace placeholder elements in email template.
        $subject = str_replace('{date}', date('Y-m-d'), $subject);
        $subject = str_replace('{name}', $character->name, $subject);
        $subject = str_replace('{monthly_rental_fee}', number_format($monthly_rental_fee, 0), $subject);
        $body = str_replace('{date}', date('Y-m-d'), $body);
        $body = str_replace('{name}', $character->name, $body);
        $body = str_replace('{refinery}', $refinery->name, $body);
        $body = str_replace('{monthly_rental_fee}', number_format($monthly_rental_fee, 0), $body);
        $mail = array(
            'body' => $body,
            'recipients' => array(
                array(
                    'recipient_id' => $renter->character_id,
                    'recipient_type' => 'character'
                )
            ),
            'subject' => $subject,
            'approved_cost' => 5000,
        );

        // Queue sending the evemail, spaced at 1 minute intervals to avoid triggering the mailspam limiter (4/min).
        SendEvemail::dispatch($mail)->delay(Carbon::now()->addMinutes($this->mail_delay));
        Log::info('GenerateRentNotification: dispatched job to send mail in ' . $this->mail_delay . ' minutes', [
            'mail' => $mail,
        ]);

    }
}
