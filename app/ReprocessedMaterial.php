<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReprocessedMaterial extends Model
{
    protected $table = 'reprocessed_materials';
    protected $primaryKey = 'materialTypeID';

    public function type()
    {
        return $this->hasOne('App\Type', 'typeID', 'materialTypeID');
    }

    public function history()
    {
        return $this->hasMany('App\ReprocessedMaterialsHistory', 'type_id', 'materialTypeID');
    }
}
