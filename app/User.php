<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slack_id',
        'slack_name',
        'team_id',
        'sex',
        'units'
    ];

    public function __construct(array $attributes = [])
    {
        if(!isset($attributes['units']) || 0 == strlen($attributes['units'])){
            //dfault units for a user are pounds
            $attributes['units'] = 'l';
        }
        parent::__construct($attributes);
    }
}
