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
        if (!isset($attributes['units']) || 0 == strlen($attributes['units'])) {
            //dfault units for a user are pounds
            $attributes['units'] = 'l';
        }
        parent::__construct($attributes);
    }

    public function team()
    {
        return $this->belongsTo('App\Team');
    }

    public function lifts()
    {
        return $this->hasMany('App\Lift');
    }

    /**
     * Gets the user's rep maxes.
     * @return Collection of \App\Lift
     */
    public function maxLifts()
    {
        $seen = [];//keys are movement ids, values are arrays of reps
        return $this->lifts()
        ->orderBy('grams', 'desc')
        ->get()
        ->reject(function ($lift) use (&$seen) {
            $return = false;//meaning keep this lift
            $movement_id = $lift->movement_id;
            $reps = $lift->reps;
            if (isset($seen[$movement_id])) {
                if (isset($seen[$movement_id][$reps])) {
                    $return = true;
                } else {
                    $seen[$movement_id][$reps] = true;
                }
            } else {
                $seen[$movement_id] = [$reps => true];
            }
            return $return;

        });
    }
}
