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

class GenerateRentalInvoice implements ShouldQueue
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

        // Calculate the amount to invoice, taking into account partial months at the start of rental agreements.
        $this_month = date('n');
        $start_month = date('n', strtotime($renter->start_date));
        $invoice_amount = $renter->monthly_rental_fee;

        if ($this_month == $start_month + 1 || ($this_month == 1 && $start_month == 12))
        {
            // Rental contract started last month, we need to add on a proportion of the monthly fee to this month's invoice.
            $start_date = date('j', strtotime($renter->start_date));
            $days_in_month = date('t', strtotime($renter->start_date));
            $extra_days_to_invoice = $days_in_month - $start_date + 1;
            $proportion_of_monthly_rent = $extra_days_to_invoice / $days_in_month;
            $additional_rent_to_charge = $renter->monthly_rental_fee * $proportion_of_monthly_rent;
            $invoice_amount += $additional_rent_to_charge;
        }

        // Round the amount so we don't have issues comparing payments without cents.
        $invoice_amount = round($invoice_amount);

        // Update the amount this renter currently owes.
        $renter->amount_owed += $invoice_amount;
        Log::info('GenerateRentalInvoice: updated stored amount owed by renter ' . $character->name . ' for refinery ' . $refinery->name . ' to ' . $renter->amount_owed);
        $renter->save();

        // Pick up the renter invoice template to apply text substitutions.
        $template = Template::where('name', 'renter_invoice')->first();
        
        // Grab the template subject and body.
        $subject = $template->subject;
        $body = $template->body;
        
        // Replace placeholder elements in email template.
        $subject = str_replace('{date}', date('Y-m-d'), $subject);
        $subject = str_replace('{name}', $character->name, $subject);
        $subject = str_replace('{amount_owed}', number_format($invoice_amount, 0), $subject);
        $body = str_replace('{date}', date('Y-m-d'), $body);
        $body = str_replace('{name}', $character->name, $body);
        $body = str_replace('{refinery}', $refinery->name, $body);
        $body = str_replace('{amount_owed}', number_format($invoice_amount, 0), $body);
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
        Log::info('GenerateRentalInvoice: dispatched job to send mail in ' . $this->mail_delay . ' minutes', [
            'mail' => $mail,
        ]);

        // Write an invoice entry.
        $invoice = new RentalInvoice;
        $invoice->renter_id = $renter->character_id;
        $invoice->refinery_id = $renter->refinery_id;
        $invoice->amount = $invoice_amount;
        $invoice->save();

        Log::info('GenerateRentalInvoice: saved new invoice for renter ' . $renter->character_id . ' at refinery ' . $renter->refinery_id . ' for amount ' . $invoice_amount);

    }

}
