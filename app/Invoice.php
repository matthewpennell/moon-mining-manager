<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    
    /**
     * Get the miner record associated with the invoice.
     */
    public function miner()
    {
        return $this->belongsTo('App\Miner');
    }

}
