<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slack_id',
        'slack_domain'
    ];

    public function users()
    {
        return $this->hasMany('App\User');
    }
}
