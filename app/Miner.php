<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Miner extends Model
{
    
    /**
     * Get the invoices for the miner.
     */
    public function invoices()
    {
        return $this->hasMany('App\Invoice');
    }

    /**
     * Get the payments for the miner.
     */
    public function payments()
    {
        return $this->hasMany('App\Payment');
    }

    /**
     * Get the mining activity for the miner.
     */
    public function mining_activity()
    {
        return $this->hasMany('App\MiningActivity');
    }

    /**
     * Get the name of the alliance for this character.
     */
    public function alliance()
    {
        return $this->belongsTo('App\Alliance', 'alliance_id', 'alliance_id')->withDefault([
            'name' => 'no alliance',
        ]);
    }

    /**
     * Get the name of the corporation of this character.
     */
    public function corporation()
    {
        return $this->belongsTo('App\Corporation', 'corporation_id', 'corporation_id');
    }

    /**
     * Return a total of all payments made by this miner.
     */
    public function getTotalPaymentsAttribute()
    {
        return DB::table('payments')->select('amount_received')->where('miner_id', $this->eve_id)->sum('amount_received');
    }

    /**
     * Return the date of the most recent payment made by this miner.
     */
    public function getLatestPaymentAttribute()
    {
        $latest_payment = DB::table('payments')->where('miner_id', $this->eve_id)->select('updated_at')->orderBy('updated_at', 'desc')->first();
        return (isset($latest_payment)) ? $latest_payment->updated_at : NULL;
    }

    /**
     * Return the date of the most recent invoice sent to this miner.
     */
    public function getLatestInvoiceAttribute()
    {
        $latest_invoice = DB::table('invoices')->where('miner_id', $this->eve_id)->select('updated_at')->orderBy('updated_at', 'desc')->first();
        return (isset($latest_invoice)) ? $latest_invoice->updated_at : NULL;
    }

    /**
     * Return the date of the most recent mining activity recorded for this miner.
     */
    public function getLatestMiningActivityAttribute()
    {
        $latest_mining_activity = DB::table('mining_activities')->where('miner_id', $this->eve_id)->select('created_at')->orderBy('created_at', 'desc')->first();
        return (isset($latest_mining_activity)) ? $latest_mining_activity->created_at : NULL;
    }

}
