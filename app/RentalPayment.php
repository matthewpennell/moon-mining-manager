<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RentalPayment extends Model
{
    /**
     * Get the refinery record associated with the invoice.
     */
    public function refinery()
    {
        return $this->hasOne('App\Refinery', 'observer_id', 'refinery_id');
    }
}
