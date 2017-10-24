<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReprocessedMaterial extends Model
{
    protected $table = 'reprocessed_materials';
    protected $primaryKey = 'materialTypeID';
}
