<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Classes\EsiConnection;
use App\Miner;
use App\Renter;
use App\Payment;
use App\Template;
use App\Jobs\SendEvemail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PollWallet implements ShouldQueue
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
        
        $esi = new EsiConnection;

        // Request the transactions from the master wallet division.
        $transactions = $esi->esi->invoke('get', '/corporations/{corporation_id}/wallets/{division}/journal/', [
            'corporation_id' => $esi->corporation_id,
            'division' => 1, // master wallet
        ]);

        Log::info('PollWallet: retrieved ' . count($transactions) . ' transactions from the corporation wallet');

        $delay_counter = 0;

        foreach ($transactions as $transaction)
        {
            if ($transaction->ref_type == 'player_donation')
            {

                // Look for matching payers among renters and miners.
                $renter = Renter::where([
                    ['character_id', $transaction->first_party_id],
                    ['amount_owed', $transaction->amount],
                ])->first();
                $miner = Miner::where('eve_id', $transaction->first_party_id)->first();
                
                // First check if the payment comes from a recognised renter and is exactly the right amount.
                if (isset($renter))
                {
                    // Clear their outstanding debt.
                    $renter->amount_owed = 0;
                    $renter->save();
                    Log::info('PollWallet: found a player donation from a recognised renter ' . $renter->character_id);

                    // Retrieve the name of the character.
                    $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
                        'character_id' => $renter->character_id,
                    ]);

                    // Send a receipt.
                    $template = Template::where('name', 'receipt')->first();
                    
                    // Replace placeholder elements in email template.
                    $template->subject = str_replace('{date}', date('Y-m-d'), $template->subject);
                    $template->subject = str_replace('{name}', $character->name, $template->subject);
                    $template->subject = str_replace('{amount}', $transaction->amount, $template->subject);
                    $template->subject = str_replace('{amount_owed}', $renter->amount_owed, $template->subject);
                    $template->body = str_replace('{date}', date('Y-m-d'), $template->body);
                    $template->body = str_replace('{name}', $character->name, $template->body);
                    $template->body = str_replace('{amount}', $transaction->amount, $template->body);
                    $template->body = str_replace('{amount_owed}', $renter->amount_owed, $template->body);
                    $mail = array(
                        'body' => $template->body,
                        'recipients' => array(
                            array(
                                'recipient_id' => $renter->character_id,
                                'recipient_type' => 'character'
                            )
                        ),
                        'subject' => $template->subject,
                        'approved_cost' => 5000,
                    );
        
                    // Queue sending the evemail, spaced at 20-second intervals to avoid triggering the mailspam limiter (4/min).
                    SendEvemail::dispatch($mail)->delay(Carbon::now()->addSeconds($delay_counter * 20));
                    $delay_counter++;
                    Log::info('PollWallet: queued job to send rental receipt evemail');
                }
                // Next, if this donation is actually from a recognised miner.
                elseif (isset($miner))
                {

                    Log::info('PollWallet: found a player donation from a recognised miner ' . $miner->eve_id);

                    // Check if this donation was already processed.
                    $payment = Payment::where('ref_id', $transaction->ref_id)->first();
                    if (!isset($payment))
                    {

                        // Record this transaction in the payments table.
                        $payment = new Payment;
                        $payment->miner_id = $transaction->first_party_id;
                        $payment->ref_id = $transaction->ref_id;
                        $payment->amount_received = $transaction->amount;
                        $payment->save();

                        Log::info('PollWallet: saved a new payment from miner ' . $miner->eve_id . ' for ' . $transaction->amount);

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
                        $delay_counter++;

                        Log::info('PollWallet: queued job to send tax receipt evemail');
                
                    }
                }
            }
        }

    }

}
