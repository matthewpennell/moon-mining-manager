<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Miner;
use App\Refinery;
use App\MiningActivity;
use App\Payment;
use App\Template;
use App\Invoice;
use App\Jobs\SendEvemail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateInvoice implements ShouldQueue
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

        // Retrieve the miner record.
        $miner = Miner::where('eve_id', $this->id)->first();
        
        // Retrieve all of this miner's mining activity, invoices and payments.
        $invoices = Invoice::where('miner_id', $miner->eve_id)->get()->keyBy('created_at')->toArray();
        $mining_activities = MiningActivity::where('miner_id', $miner->eve_id)->get()->keyBy('created_at')->toArray();
        $payments = Payment::where('miner_id', $miner->eve_id)->get()->keyBy('created_at')->toArray();
        $combined = array_merge($invoices, $mining_activities, $payments);

        // Sort the log by reverse chronological order.
        krsort($combined);

        // Loop through the combined list, creating each record into a human-readable line in the activity log.
        $activity_log = [];
        foreach ($combined as $date => $event)
        {
            if (isset($event['amount']))
            {
                $activity_log[] = date('Y-m-d', strtotime($date)) . ' - Invoice sent for ' . number_format($event['amount']) . ' ISK';
            }
            if (isset($event['quantity']))
            {
                $refinery = Refinery::where('observer_id', $event['refinery_id'])->first();
                $activity_log[] = date('Y-m-d', strtotime($date)) . ' - Mining recorded in ' . $refinery->system->solarSystemName . ' (' . $event['quantity'] . ' units)';
            }
            if (isset($event['amount_received']))
            {
                $activity_log[] = date('Y-m-d', strtotime($date)) . ' - Payment received for ' . number_format($event['amount_received']) . ' ISK';
            }
        }

        // Pick up the invoice template to apply text substitutions.
        $template = Template::where('name', 'weekly_invoice')->first();
        
        // Grab the template subject and body.
        $subject = $template->subject;
        $body = $template->body;
        
        // Replace placeholder elements in email template.
        $subject = str_replace('{date}', date('Y-m-d'), $subject);
        $subject = str_replace('{name}', $miner->name, $subject);
        $subject = str_replace('{amount_owed}', number_format($miner->amount_owed, 0), $subject);
        $body = str_replace('{date}', date('Y-m-d'), $body);
        $body = str_replace('{name}', $miner->name, $body);
        $body = str_replace('{amount_owed}', number_format($miner->amount_owed, 0), $body);
        $body = str_replace('{activity_log}', implode("\n", $activity_log), $body);
        $mail = array(
            'body' => $body,
            'recipients' => array(
                array(
                    'recipient_id' => $miner->eve_id,
                    'recipient_type' => 'character'
                )
            ),
            'subject' => $subject,
        );

        // Queue sending the evemail, spaced at 20-second intervals to avoid triggering the mailspam limiter (4/min).
        SendEvemail::dispatch($mail)->delay(Carbon::now()->addSeconds($this->mail_delay));
        Log::info('GenerateInvoice: dispatched job to send mail in ' . $this->mail_delay . ' seconds', [
            'mail' => $mail,
        ]);

        // Write an invoice entry.
        $invoice = new Invoice;
        $invoice->miner_id = $miner->eve_id;
        $invoice->amount = $miner->amount_owed;
        $invoice->save();

        Log::info('GenerateInvoice: saved new invoice for miner ' . $miner->eve_id . ' for amount ' . $miner->amount_owed);

    }

}
