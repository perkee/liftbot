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
        'bodygrams',
        'reps',
        'url'
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

    public function __toString()
    {
        $units = isset($this->units) ? $this->units : 'l';
        return $this->movement->name . ': ' . $this->convert($units) . " × $this->reps";
    }

    public function convert($units = '', $fallBackUnits = 'l')
    {
        $units = ''.$units.$fallBackUnits;
        switch (substr($units, 0, 1)) {
            case 'l': //intentional fallthrough for synonyms
            case '#':
                return $this->lb().' lb';
            case 'k':
                return $this->kg().' kg';
            default:
                return false;
                break;
        }
    }

    private function floatToString($float){
        return sprintf('%.1f',$float);
    }


    public function kg(){
        return $this->floatToString($this->grams / 1000);
    }

    public function lb(){
        return $this->floatToString($this->grams / 453.593);
    }
}
