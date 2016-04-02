<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lift extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'movement_id',
        'grams',
        'bodygrams'
    ];

    /**
     * Get the user who performed this lift
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the Movement of which this lift is an instance
     */
    public function movement()
    {
        return $this->belongsTo('App\Movement');
    }
}
