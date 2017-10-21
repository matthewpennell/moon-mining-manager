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
     * Return a total of all payments made by this miner.
     */
    public function getTotalPaymentsAttribute()
    {
        return DB::table('payments')->select('amount_received')->where('miner_id', $this->eve_id)->sum('amount_received');
    }

}
