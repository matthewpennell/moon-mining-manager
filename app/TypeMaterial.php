<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypeMaterial extends Model
{
    protected $table = 'invTypeMaterials';
    public $incrementing = false;
    public $timestamps = false;

    public function type()
    {
        return $this->belongsTo('App\Type', 'typeID', 'typeID');
    }

}
