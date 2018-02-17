<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moon extends Model
{
    
    /**
     * Get the solar system where this moon is located.
     */
    public function system()
    {
        return $this->belongsTo('App\SolarSystem', 'solar_system_id');
    }

    /**
     * Get the region this moon is part of.
     */
    public function region()
    {
        return $this->belongsTo('App\Region', 'region_id');
    }

    /**
     * Get the mineral type object for each of the possible mineral types.
     */
    public function mineral_1()
    {
        return $this->belongsTo('App\Type', 'mineral_1_type_id');
    }

    public function mineral_2()
    {
        return $this->belongsTo('App\Type', 'mineral_2_type_id');
    }

    public function mineral_3()
    {
        return $this->belongsTo('App\Type', 'mineral_3_type_id');
    }

    public function mineral_4()
    {
        return $this->belongsTo('App\Type', 'mineral_4_type_id');
    }

}
