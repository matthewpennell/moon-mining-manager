<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{

    protected $table = 'mapRegions';
    protected $primaryKey = 'regionID';
    public $incrementing = false;
    public $timestamps = false;

    public function systems()
    {
        return $this->hasMany('App\SolarSystem', 'regionID');
    }

}
