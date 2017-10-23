<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    
    protected $table = 'invTypes';
    protected $primaryKey = 'typeID';
    public $incrementing = false;
    public $timestamps = false;

    public function type_materials()
    {
        return $this->hasMany('App\TypeMaterial', 'typeID', 'typeID');
    }

}
