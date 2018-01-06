<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RentalInvoice extends Model
{
    /**
     * Get the refinery record associated with the invoice.
     */
    public function refinery()
    {
        return $this->hasOne('App\Refinery', 'observer_id', 'refinery_id');
    }
}
