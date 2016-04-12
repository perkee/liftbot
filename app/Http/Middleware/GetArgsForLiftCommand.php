<?php

namespace App\Http\Middleware;

use \Illuminate\Http\Request;
use Closure;
use Log;

class GetArgsForLiftCommand
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $input = $request->all();
        if(isset($input['text']) && isset($input['command']) && 'lift' == $input['command']){
            $text = $input['text'];
            if(!$text){
                die('Lift needs a movement and weight at least');
            }
            //full string looks like /perk lift <movement name> <Weight>
            //reusable array for running preg_match
            $matches = [];
            //patterns we need to use twice to get values then remove from input
            $regex = (object)[
                'reps'   => '/[x*]\s*\d+/i',
                'weight' => '/(?<![\w.\/])\s*(@?)\s*(\d+)\s*(kg?|lb?|#)?s?\s*/',
                'url'    => '/https?:\/\/[-.a-zA-Z\/\d?=_]+/'
            ];
            //get number of reps
            $reps = 1;
            preg_match($regex->reps,$text,$matches);
            if(isset($matches[0])){
                $reps = + substr($matches[0],1);
                $text = preg_replace($regex->reps, '', $text);
            }

            //get weights

            preg_match_all($regex->weight,$text,$matches);

            //matches has four arrays: first is full matches, others match groups:
            // 0 => array for full matches
            // 1 => BW flag group: - string matching @ or blank
            // 2 => magnitude group: string comprised of digits
            // 3 => units group indicating kg or lb
            if(4 >= count($matches) ){
                $input['weights'] = $matches;
                $bw = $matches[1];
                $magnitudes = $matches[2];
                $units = $matches[3];
                foreach ($magnitudes as $idx => $magnitude) {
                    $grams = $this->toGrams($magnitude,$units[$idx],$request->user->units);
                    if($this->isBodyWeight($bw[$idx])){
                        $input['bodyGrams'] = $grams;
                    }
                    else{
                        $input['grams'] = $grams;
                    }
                }
            }

            //drop weights
            $text = preg_replace($regex->weight, '', $text);

            //get URL
            preg_match($regex->url, $text, $matches);
            if(count($matches) > 0){
                $input['url'] = $matches[0];
                $text = preg_replace($regex->url,'', $text);
            }

            //finally pull the movement name out
            $movementName = preg_replace('/^ *([- a-zA-Z_]+)([-a-zA-Z_]+).*/', '$1$2' , $text);
            
            //Drop movement name from the start of the command
            $text = preg_replace('/^[^\d]*/', '' , $text);

            $input['movementName'] = $movementName;
            $input['reps'] = $reps;
            $input['text'] = $text;
            if($this->isValid($input)){
                $request->replace($input);
            }
            else{
                $keys = array_keys($input);
                $keys = implode(', ', $keys);
                throw new \Exception("Invalid lift command, you provided these so what's missing?\n$keys", 1);
            }

        }
        return $next($request);
    }

    protected function isValid(&$input = []){
        $input['movementName'] = trim($input['movementName']);
        if(empty($input['movementName'])){
            unset($input['movementName']);
        }
        return isset(
            $input['movementName'],
            $input['reps'],
            $input['grams']
        ) &&
        is_numeric($input['reps'])     && 
        is_numeric($input['grams'])
        ;
    }

    protected function toGrams($magnitude = 0, $units = '', $fallBackUnits = 'l'){
        $units .= $fallBackUnits;
        switch (substr($units, 0, 1)) {
            case 'l': //intentional fallthrough for synonyms
            case '#':
                return $this->lbToGrams($magnitude);
                break;

            case 'k':
                return $this->kgToGrams($magnitude);
            default:
                return false;
                break;
        }
    }


    private function isBodyWeight($weightString){
        return 0 === strncmp($weightString, '@', 1);
    }

    private function kgToGrams($kg){
        return 1000 * $kg;
    }

    private function lbToGrams($lb){
        return $lb * 453.593;
    }
}
