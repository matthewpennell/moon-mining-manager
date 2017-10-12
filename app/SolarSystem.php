<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolarSystem extends Model
{
    
    protected $table = 'mapSolarSystems';
    protected $primaryKey = 'solarSystemID';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * Get the mining activity for the refinery.
     */
    public function refinery()
    {
        return $this->hasMany('App\Refinery');
    }


}
