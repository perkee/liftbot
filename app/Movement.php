<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'name',
        //'hash'
    ];

    public static function fromName($name){
        $hash = self::makeHash($name);
        $movement = self::where('hash', $hash);
        if( 0 === $movement->count() ){
            $movement = new Movement(['name' => $name, 
                //'hash' => $hash
                ]);
            $movement->save();
        }
        else{
            $movement = $movement->first();
        }
        return $movement;
    }

    public function setNameAttribute($val){
        $this->attributes['name'] = $val;
        $this->attributes['hash'] = self::makeHash($val);
    }

    static function makeHash($name){
        
        $hash = strtolower(trim($name));
        $hash = preg_replace('/[^a-z]/', '', $name);
        return $hash;
    }
}
