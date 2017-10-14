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

}
