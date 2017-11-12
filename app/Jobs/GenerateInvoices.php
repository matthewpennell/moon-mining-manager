<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TaxRate;
use App\MiningActivity;
use App\Type;
use App\Miner;
use App\Refinery;
use App\Template;
use App\Invoice;
use Ixudra\Curl\Facades\Curl;
use App\Jobs\SendEvemail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('GenerateInvoices: starting...');

        // Array to hold all of the information we want to send by invoice.
        $invoice_data = [];
        // Create arrays to hold miner and refinery details. We'll write it back to the database when we're done.
        $miner_data = [];
        $refinery_data = [];
        $miner_activity_data = [];
        
        // Grab all of the ore values and tax rates to refer to in calculations. This
        // returns an array keyed by type_id, so individual values/tax rates can be returned
        // by reference to $tax_rates[type_id]->value or $tax_rates[type_id]->tax_rate.
        $tax_rates = TaxRate::select('type_id', 'value', 'tax_rate')->get()->keyBy('type_id');

        // Grab all of the unprocessed mining activity records and loop through them.
        $activity = MiningActivity::where('processed', 0)->get();

        Log::info('GenerateInvoices: found ' . count($activity) . ' mining activity entries to process');

        foreach ($activity as $entry)
        {
            // Each mining activity relates to a single ore type.
            // We calculate the total value of that activity, and apply the 
            // current tax rate to derive a tax amount to charge.
            $total_value = $entry->quantity * $tax_rates[$entry->type_id]->value;
            $tax_amount = $total_value * $tax_rates[$entry->type_id]->tax_rate / 100;

            // Add the tax amount for this entry to the miner array.
            if (isset($miner_data[$entry->miner_id]))
            {
                $miner_data[$entry->miner_id] += $tax_amount;
                $miner_activity_data[$entry->miner_id][] = 'Mining near ' . $entry->refinery->name . ' on ' . date('M j, Y', strtotime($entry->updated_at));
            }
            else
            {
                $miner_data[$entry->miner_id] = $tax_amount;
                $miner_activity_data[$entry->miner_id] = ['Mining near ' . $entry->refinery->name . ' on ' . date('M j, Y', strtotime($entry->updated_at))];
            }

            // Add the income for this entry to the refinery array.
            if (isset($refinery_data[$entry->refinery_id]))
            {
                $refinery_data[$entry->refinery_id] += $tax_amount;
            }
            else
            {
                $refinery_data[$entry->refinery_id] = $tax_amount;
            }

            $entry->processed = 1;
            $entry->save(); // this might be expensive, maybe update them all at the end?
        }

        // Loop through all of the miner data and update the database records.
        if (count($miner_data))
        {
            foreach ($miner_data as $key => $value)
            {
                $miner = Miner::where('eve_id', $key)->first();
                $miner->amount_owed += $value;
                $activity_log = implode("\n", $miner_activity_data[$key]);
                $miner->activity_log = ($miner->activity_log == NULL) ? $activity_log : $miner->activity_log + $activity_log;
                $miner->save();
                Log::info('GenerateInvoices: updated stored amount owed and recent activity by miner ' . $key);
            }
        }

        // Loop through all the refinery data and update the database records.
        if (count($refinery_data))
        {
            foreach ($refinery_data as $key => $value)
            {
                $refinery = Refinery::where('observer_id', $key)->first();
                $refinery->income += $value;
                $refinery->save();
                Log::info('GenerateInvoices: updated stored amount generated by refinery ' . $key);
            }
        }

        // For all miners in your alliance that currently owe an outstanding balance, generate and send an invoice.
        $debtors = Miner::where('amount_owed', '>', 0)->where('alliance_id', env('EVE_ALLIANCE_ID'))->get();
        Log::info('GenerateInvoices: found ' . count($debtors) . ' miners with an outstanding balance to be invoiced');
        $template = Template::where('name', 'weekly_invoice')->first();
        $delay_counter = 0;

        foreach ($debtors as $miner)
        {

            // Grab the default template subject and body.
            $subject = $template->subject;
            $body = $template->body;

            // Replace placeholder elements in email template.
            $subject = str_replace('{date}', date('Y-m-d'), $subject);
            $subject = str_replace('{name}', $miner->name, $subject);
            $subject = str_replace('{amount_owed}', number_format($miner->amount_owed, 0), $subject);
            $body = str_replace('{date}', date('Y-m-d'), $body);
            $body = str_replace('{name}', $miner->name, $body);
            $body = str_replace('{amount_owed}', number_format($miner->amount_owed, 0), $body);
            $body = str_replace('{activity_log}', $miner->activity_log, $body);
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
            SendEvemail::dispatch($mail)->delay(Carbon::now()->addSeconds($delay_counter * 20));
            Log::info('GenerateInvoices: dispatched job to send mail in ' . ($delay_counter * 20) . ' seconds');
            $delay_counter++;

            // Write an invoice entry.
            $invoice = new Invoice;
            $invoice->miner_id = $miner->eve_id;
            $invoice->amount = $miner->amount_owed;
            $invoice->save();

            Log::info('GenerateInvoices: saved new invoice for miner ' . $miner->eve_id . ' for amount ' . $miner->amount_owed);

        }

    }

}
