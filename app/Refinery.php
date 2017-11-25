<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Refinery extends Model
{
    
    protected $table = 'refineries';

    /**
     * Get the mining activity for the refinery.
     */
    public function mining_activity()
    {
        return $this->hasMany('App\MiningActivity', 'refinery_id', 'observer_id');
    }

    /**
     * Get the solar system record associated with the activity.
     */
    public function system()
    {
        return $this->belongsTo('App\SolarSystem', 'solar_system_id');
    }

    /**
     * Get the user record for the primary detonation character.
     */
    public function primary()
    {
        return $this->belongsTo('App\User', 'claimed_by_primary', 'eve_id');
    }

    /**
     * Get the user record for the secondary detonation character.
     */
    public function secondary()
    {
        return $this->belongsTo('App\User', 'claimed_by_secondary', 'eve_id');
    }

}
