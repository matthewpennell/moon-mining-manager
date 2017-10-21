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

}
