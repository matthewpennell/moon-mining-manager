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
use App\RentalPayment;
use App\Template;
use App\Jobs\SendEvemail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PollWallet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;
    private $page;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($page = 1)
    {
        $this->page = $page;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $esi = new EsiConnection;

        Log::info('PollWallet: Retrieving transactions, page ' . $this->page);

        // Request the transactions from the master wallet division.
        $transactions = $esi->esi->setQueryString([
            'page' => $this->page,
        ])->invoke('get', '/corporations/{corporation_id}/wallets/{division}/journal/', [
            'corporation_id' => $esi->corporation_id,
            'division' => 1, // master wallet
        ]);

        Log::info('PollWallet: retrieved ' . count($transactions) . ' transactions from the corporation wallet');

        $delay_counter = 1;
        $date = NULL;

        foreach ($transactions as $transaction)
        {
            $ref_id = $transaction->id;
            $date = date('Y-m-d', strtotime($transaction->date));
            if ($transaction->ref_type == 'player_donation')
            {

                // Look for matching payers among renters and miners.
                $renter = Renter::where([
                    ['character_id', $transaction->first_party_id],
                    ['amount_owed', $transaction->amount],
                ])->first();
                $miner = Miner::where('eve_id', $transaction->first_party_id)->first();
                
                // First check if the payment comes from a recognised renter and is exactly the right amount for an outstanding refinery balance.
                if (isset($renter))
                {

                    // Record this transaction in the rental_payments table.
                    $payment = new RentalPayment;
                    $payment->renter_id = $transaction->first_party_id;
                    $payment->refinery_id = $renter->refinery_id;
                    $payment->ref_id = $ref_id;
                    $payment->amount_received = $transaction->amount;
                    $payment->save();

                    // Clear their outstanding debt.
                    $renter->amount_owed = 0;
                    $renter->save();
                    Log::info('PollWallet: saved a new payment from renter ' . $renter->character_id . ' at refinery ' . $renter->refinery_id . ' for ' . $transaction->amount);

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
        
                    // Queue sending the evemail, spaced at 1-minute intervals to avoid triggering the mailspam limiter (4/min).
                    SendEvemail::dispatch($mail)->delay(Carbon::now()->addMinutes($delay_counter));
                    $delay_counter++;
                    Log::info('PollWallet: queued job to send rental receipt evemail');
                }
                // Next, if this donation is actually from a recognised miner.
                elseif (isset($miner))
                {

                    Log::info('PollWallet: found a player donation of ' . $transaction->amount . ' ISK from a recognised miner ' . $miner->eve_id . ' on ' . $date . ', reference ' . $ref_id);

                    // Check if this donation was already processed.
                    $payment = Payment::where('ref_id', $ref_id)->first();
                    $rental_payment = RentalPayment::where('ref_id', $ref_id)->first();
                    if (!isset($payment) && !isset($rental_payment))
                    {

                        // Parse the 'reason' entered by the player to see if they want to pay off other players/alts bills.
                        $reason = $transaction->reason;
                        if ($reason && strlen($reason) > 0)
                        {
                            // Split by commas.
                            $elements = explode(',', $reason);
                            $recipients = [];

                            // For each element found, test it to see if it is a character ID or name, and find a reference to the relevant miner.
                            foreach ($elements as $element)
                            {
                                if (preg_match('/^\d+$/', trim($element)))
                                {
                                    $recipient_miner = Miner::where('eve_id', trim($element))->first();
                                }
                                else
                                {
                                    $recipient_miner = Miner::where('name', trim($element))->first();
                                }
                                if (isset($recipient_miner))
                                {
                                    $recipients[] = $recipient_miner;
                                }
                            }
                            Log::info('PollWallet: detected player-entered reason for payment, parsed for alternative recipients of payment, found ' . count($recipients) . ' additional valid recipients', [
                                'recipients' => $recipients
                            ]);

                            // If any valid recipients were found, create payments to them.
                            if (count($recipients) > 0)
                            {
                                foreach ($recipients as $recipient)
                                {
                                    // Calculate how much to pay off for this recipient - either the full amount, or whatever is left of the balance.
                                    if ($transaction->amount >= $recipient->amount_owed)
                                    {
                                        $payment_amount = $recipient->amount_owed;
                                    }
                                    else
                                    {
                                        $payment_amount = $transaction->amount;
                                    }

                                    // Update the remaining balance of what was sent.
                                    $transaction->amount = $transaction->amount - $payment_amount;

                                    // Record the transaction in the payments table.
                                    $payment = new Payment;
                                    $payment->miner_id = $recipient->eve_id;
                                    $payment->ref_id = $ref_id;
                                    $payment->amount_received = $payment_amount;
                                    $payment->save();
                                    Log::info('PollWallet: saved a new payment from miner ' . $miner->eve_id . ' on behalf of miner ' . $recipient->eve_id . ' for ' . $payment_amount);

                                    // Deduct the amount from the recipient's outstanding balance.
                                    $recipient->amount_owed -= $payment_amount;
                                    $recipient->save();
                                }
                            }
                        }

                        // If there is any money left to apply to the donator's balance after paying other recipients.
                        if ($transaction->amount > 0)
                        {
                            // Record this transaction in the payments table.
                            $payment = new Payment;
                            $payment->miner_id = $transaction->first_party_id;
                            $payment->ref_id = $ref_id;
                            $payment->amount_received = $transaction->amount;
                            $payment->save();

                            Log::info('PollWallet: saved a new payment from miner ' . $miner->eve_id . ' for ' . $transaction->amount);

                            // Deduct the amount from their outstanding balance.
                            $miner->amount_owed -= $transaction->amount;
                            $miner->save();
                        }

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
            
                        // Queue sending the evemail, spaced at 1 minute intervals to avoid triggering the mailspam limiter (4/min).
                        SendEvemail::dispatch($mail)->delay(Carbon::now()->addMinutes($delay_counter));
                        Log::info('PollWallet: queued job to send tax receipt evemail in ' . $delay_counter . ' minutes');
                        $delay_counter++;
                
                    }
                }
            }

        }

        /* FIX SCRIPT FOR UNPROCESSED WALLET TRANSACTIONS, IF NEEDED UPDATE THE DATE TO THE LAST WORKING WALLET IMPORT. 

        // If the last transaction date is not earlier than a specified date, request the next page of wallet results.
        if (isset($date) && $date > '2018-04-25')
        {
            Log::info('PollWallet: Date ' . $date . ' is greater than 2018-04-25, repolling for any earlier transactions');
            $next_page = $this->page + 1;
            PollWallet::dispatch($next_page);
        }
        else
        {
            Log::info('PollWallet: No more wallet transactions to be found or date reached');
        }

        // ENDFIX */

    }

}
