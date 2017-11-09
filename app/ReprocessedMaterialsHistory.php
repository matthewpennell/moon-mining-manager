<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReprocessedMaterialsHistory extends Model
{
    protected $table = 'reprocessed_materials_history';

    public function reprocessed_material()
    {
        return $this->belongsTo('App\ReprocessedMaterial', 'materialTypeID');
    }
}
