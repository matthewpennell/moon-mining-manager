<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Miner;
use App\Payment;
use App\Template;
use App\Jobs\SendEvemail;

class PollWallet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        
        $esi = new EsiConnection;

        // Request the transactions from the master wallet division.
        $transactions = $esi->esi->invoke('get', '/corporations/{corporation_id}/wallets/{division}/journal/', [
            'corporation_id' => $esi->corporation_id,
            'division' => 1, // master wallet
        ]);

        $delay_counter = 0;

        foreach ($transactions as $transaction)
        {
            if ($transaction->ref_type == 'player_donation')
            {
                echo 'player_donation found!<br>';
                // Check if this donation is actually from a recognised miner.
                $miner = Miner::where('eve_id', $transaction->first_party_id)->first();
                if (isset($miner))
                {
                    echo 'valid miner found!<br>';
                    // Check if this donation was already processed.
                    $payment = Payment::where('ref_id', $transaction->ref_id)->first();
                    if (!isset($payment))
                    {

                        echo 'no record of this payment<br>';

                        // Record this transaction in the payments table.
                        $payment = new Payment;
                        $payment->miner_id = $transaction->first_party_id;
                        $payment->ref_id = $transaction->ref_id;
                        $payment->amount = $transaction->amount;
                        $payment->save();

                        // Deduct the amount from their outstanding balance.
                        $miner->amount_owed -= $transaction->amount;
                        $miner->save();

                        // Send a receipt.
                        $template = Template::where('name', 'receipt')->first();

                        // Replace placeholder elements in email template.
                        $template->subject = str_replace('{date}', date('Y-m-d'), $template->subject);
                        $template->subject = str_replace('{name}', $miner->name, $template->subject);
                        $template->subject = str_replace('{amount}', $transaction->amount, $template->subject);
                        $template->subject = str_replace('{amount_owed}', $miner->amount_owed, $template->subject);
                        $template->body = str_replace('{date}', date('Y-m-d'), $template->body);
                        $template->body = str_replace('{name}', $miner->name, $template->body);
                        $template->body = str_replace('{amount}', $transaction->amount, $template->body);
                        $template->body = str_replace('{amount_owed}', $miner->amount_owed, $template->body);
                        $mail = array(
                            'body' => $template->body,
                            'recipients' => array(
                                array(
                                    'recipient_id' => $miner->eve_id,
                                    'recipient_type' => 'character'
                                )
                            ),
                            'subject' => $template->subject,
                        );
            
                        // Queue sending the evemail, spaced at 20-second intervals to avoid triggering the mailspam limiter (4/min).
                        SendEvemail::dispatch($mail)->delay(Carbon::now()->addSeconds($delay_counter * 20));
                        echo 'queued email for sending<br>';
                        $delay_counter++;
                
                    }
                }
            }
        }

    }

}
