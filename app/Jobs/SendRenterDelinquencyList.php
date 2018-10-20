<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Renter;
use App\Refinery;
use App\Jobs\SendEvemail;
use App\Classes\EsiConnection;
use Illuminate\Support\Facades\Log;

class SendRenterDelinquencyList implements ShouldQueue
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
        // ESI connection.
        $esi = new EsiConnection;

        // Grab all the renters with active agreements where they currently have an outstanding balance.
        $renters = Renter::whereRaw('refinery_id IS NOT NULL && start_date <= CURDATE() AND (end_date IS NULL OR end_date >= CURDATE()) AND amount_owed > 0')->get();

        // Generate the email to send to the administrator.
        $subject = 'Moon Rental Delinquency Report for ' . date('Y-m-d');
        $body = "The following renters have not paid off their outstanding rental balance yet:\n\n";
        foreach ($renters as $renter)
        {
            // Request the character name for this rental agreement.
            $character = $esi->esi->invoke('get', '/characters/{character_id}/', [
                'character_id' => $renter->character_id,
            ]);
            // Grab a reference to the refinery that is being rented.
            $refinery = Refinery::where('observer_id', $renter->refinery_id)->first();
            // Output the details of this renter to the email body.
            $body .= 'Renter: <url=showinfo:1376//93533671>' . $character->name . '</url>';
            $body .= "\n";
            $body .= 'Refinery: <loc><url=http://http://goo.bravecollective.com/renters/refinery/' . $renter->refinery_id . '>' . $refinery->name . '</url></loc>';
            $body .= "\n";
            $body .= 'Balance: ' . number_format($renter->amount_owed, 0) . ' ISK';
            $body .= "\n\n";
        }
        $mail = array(
            'body' => $body,
            'recipients' => array(
                array(
                    'recipient_id' => env('ADMIN_USER_ID', 0),
                    'recipient_type' => 'character'
                )
            ),
            'subject' => $subject,
            'approved_cost' => 5000,
        );

        // Queue sending the evemail, spaced at 1 minute intervals to avoid triggering the mailspam limiter (4/min).
        SendEvemail::dispatch($mail);
        Log::info('SendRenterDelinquencyList: dispatched job to send mail', [
            'mail' => $mail,
        ]);

    }
}
