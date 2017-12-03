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
use Carbon\Carbon;
use App\Classes\EsiConnection;
use Illuminate\Support\Facades\Log;

class GenerateRentalInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;
    private $renter;
    private $mail_delay;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($renter, $mail_delay = 20)
    {
        $this->renter = $renter;
        $this->mail_delay = $mail_delay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        // Request the character name for this rental agreement.
        $esi = new EsiConnection;
        $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $this->renter->character_id,
        ]);

        // Grab a reference to the refinery that is being rented.
        $refinery = Refinery::where('observer_id', $this->renter->refinery_id)->first();

        // Calculate the amount to invoice, taking into account partial months at the start of rental agreements.
        $this_month = date('n');
        $start_month = date('n', strtotime($this->renter->start_date));
        if ($this_month == $start_month + 1 || ($this_month == 1 && $start_month == 12))
        {
            // Rental contract started last month, we need to add on a proportion of the monthly fee to this month's invoice.
            $start_date = date('j', strtotime($this->renter->start_date));
            $days_in_month = date('t', strtotime($this->renter->start_date));
            $extra_days_to_invoice = $days_in_month - $start_date + 1;
            $proportion_of_monthly_rent = $extra_days_to_invoice / $days_in_month;
            $additional_rent_to_charge = $this->renter->monthly_rental_fee * $proportion_of_monthly_rent;
            $this->renter->monthly_rental_fee += $additional_rent_to_charge;
        }

        // Pick up the renter invoice template to apply text substitutions.
        $template = Template::where('name', 'renter_invoice')->first();
        
        // Grab the template subject and body.
        $subject = $template->subject;
        $body = $template->body;
        
        // Replace placeholder elements in email template.
        $subject = str_replace('{date}', date('Y-m-d'), $subject);
        $subject = str_replace('{name}', $character->name, $subject);
        $subject = str_replace('{amount_owed}', number_format($this->renter->monthly_rental_fee, 0), $subject);
        $body = str_replace('{date}', date('Y-m-d'), $body);
        $body = str_replace('{name}', $character->name, $body);
        $body = str_replace('{refinery}', $refinery->name, $body);
        $body = str_replace('{amount_owed}', number_format($this->renter->monthly_rental_fee, 0), $body);
        $mail = array(
            'body' => $body,
            'recipients' => array(
                array(
                    'recipient_id' => $this->renter->character_id,
                    'recipient_type' => 'character'
                )
            ),
            'subject' => $subject,
        );

        // Queue sending the evemail, spaced at 20-second intervals to avoid triggering the mailspam limiter (4/min).
        SendEvemail::dispatch($mail)->delay(Carbon::now()->addSeconds($this->mail_delay));
        Log::info('GenerateRentalInvoice: dispatched job to send mail in ' . $this->mail_delay . ' seconds', [
            'mail' => $mail,
        ]);

    }

}
