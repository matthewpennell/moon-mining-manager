<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    
    /**
     * Get the miner record associated with the payment.
     */
    public function miner()
    {
        return $this->belongsTo('App\Miner');
    }

}
