<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Renter extends Model
{
    protected $table = 'renters';

    /**
     * Get the refinery being rented.
     */
    public function refinery()
    {
        return $this->hasOne('App\Refinery', 'observer_id', 'refinery_id');
    }

}
