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

    /**
     * Get the moon where this refinery is located.
     */
    public function moon()
    {
        return $this->hasOne('App\Moon', 'id', 'moon_id');
    }



}
