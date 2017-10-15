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
        return $this->hasMany('App\MiningActivity');
    }

    /**
     * Get the solar system record associated with the activity.
     */
    public function system()
    {
        return $this->belongsTo('App\SolarSystem', 'solar_system_id');
    }

}
