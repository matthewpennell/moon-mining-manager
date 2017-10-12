<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MiningActivity extends Model
{
    
    protected $table = 'mining_activities';

    /**
     * Get the miner record associated with the activity.
     */
    public function miner()
    {
        return $this->belongsTo('App\Miner');
    }

    /**
     * Get the refinery record associated with the activity.
     */
    public function refinery()
    {
        return $this->belongsTo('App\Refinery');
    }

    /**
     * Get the type record associated with the activity.
     */
    public function type()
    {
        return $this->belongsTo('App\Type', 'type_id', 'typeID');
    }

}
