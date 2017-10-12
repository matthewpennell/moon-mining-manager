<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Whitelist extends Model
{
    
    protected $table = 'whitelist';

    /**
     * Get the user record associated with the user.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'eve_id', 'eve_id');
    }

    /**
     * Get the user record associated with the user who added this user.
     */
    public function whitelister()
    {
        return $this->belongsTo('App\User', 'added_by', 'eve_id');
    }

}
