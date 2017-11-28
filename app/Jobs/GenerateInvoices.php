<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Miner;
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Build the WHERE clause to filter by alliance and/or corporation membership.
        $whitelist_where = [];
        if (env('EVE_ALLIANCES_WHITELIST'))
        {
            $whitelist_where[] = 'alliance_id IN (' . env('EVE_ALLIANCES_WHITELIST') . ')';
        }
        if (env('EVE_CORPORATIONS_WHITELIST'))
        {
            $whitelist_where[] = 'corporation_id IN (' . env('EVE_CORPORATIONS_WHITELIST') . ')';
        }
        if (count($whitelist_where))
        {
            $whitelist_whereRaw = '(' . implode(' OR ', $whitelist_where) . ')';
        }

        // For all miners in your whitelisted alliances/corporations that currently owe an outstanding balance, generate and send an invoice.
        $debtors = Miner::where('amount_owed', '>=', 1)->whereRaw($whitelist_whereRaw)->get();
        Log::info('GenerateInvoices: found ' . count($debtors) . ' miners with an outstanding balance to be invoiced');

        // Pick up the invoice template to apply text substitutions.
        $template = Template::where('name', 'weekly_invoice')->first();

        $delay_counter = 0;

        foreach ($debtors as $miner)
        {

            // Grab the default template subject and body.
            $subject = $template->subject;
            $body = $template->body;

            // TODO: build activity log based on mining, payments, invoices (possibly limited by date).
            $activity_log = '';

            // Replace placeholder elements in email template.
            $subject = str_replace('{date}', date('Y-m-d'), $subject);
            $subject = str_replace('{name}', $miner->name, $subject);
            $subject = str_replace('{amount_owed}', number_format($miner->amount_owed, 0), $subject);
            $body = str_replace('{date}', date('Y-m-d'), $body);
            $body = str_replace('{name}', $miner->name, $body);
            $body = str_replace('{amount_owed}', number_format($miner->amount_owed, 0), $body);
            $body = str_replace('{activity_log}', $activity_log, $body);
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
