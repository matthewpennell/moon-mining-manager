<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $table = 'taxes';

    /**
     * Get the type record associated with the tax rate.
     */
    public function type()
    {
        return $this->belongsTo('App\Type', 'type_id');
    }

    /**
     * Get the reprocessed material records associated with the tax rate.
     */
    public function reprocessed_materials()
    {
        return $this->hasMany('App\TypeMaterial', 'typeID', 'type_id');
    }

}
