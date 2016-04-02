<?php

namespace App\Http\Middleware;

use Closure;

class RouteFromSlackText
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
        //die(json_encode($request->all()));
        $input = $request->all();
        $input['argc'] = 0;
        $text = '';
        if(isset( $input['text'] )){
            $text = trim($input['text']);
        }
        else{
            throw new Exception;
        }
        if('' != $text){
            $text = explode(' ',$text,2);
            if(0 === count($text) || '' === $text[0]){
                $input['argc'] = 0;
            }
            if(1 === count($text)){
                //command with no arguments means that we have a "read only" command
                $input['argc'] = 1;
            }
            elseif(2 === count($text)){
                //command comes in like "<command> <variable arguments needing changes>";
                //so let's separate out the command for starters;
                $input['argc'] = 2;
                $command = $text[0];
                $args = $text[1];

                $delim = '|';

                //get all the words before first number as a distinct argument

                $openingWord = preg_replace('/^ *([- a-zA-Z_]+)([-a-zA-Z_]+).*/', '$1$2' , $args);

                //drop the first word from the args;
                $filtered = preg_replace('/^[^\d]*(\d)/','$1', $args);

                //collapse spaces between digits and units and delimit distinct quantities
                $filtered = preg_replace('/\s*(@*)\s*(\d+)\s*(kg|lb|#)?\s*/', $delim. '$1$2$3', $filtered);

                //if filtered input looks like a lifted weight at a bodyweight,
                //prepare that data for the controller

                if(preg_match("/(${delim}@?\d+(kg|lb|#))+/", $filtered)){
                    // make into an array
                    $weights = explode($delim, $filtered);
                    foreach ($weights as $weight) {
                        if(!$weight) continue; //skip empty between delimitersd
                        $key = $this->isBodyWeight($weight) ? 'bodygrams' : 'grams';
                        $grams = $this->weightStringToGrams($weight);
                        $input[$key] = $grams;
                    }
                }

                $filtered = substr($filtered, 1);

                $input['openingWord'] = $openingWord;
                $input['args'] = $args;
                $input['command'] = $command;
                $input['filtered'] = $filtered;

            }
        }
        $request->replace($input);

        return $next($request);
    }

    private function weightStringToGrams(String $weightString, String $unitGuess = 'lb'){
        $matches = [];
        $units = $unitGuess;
        $value = 0;
        preg_match('/(\d+)(\w+)$/',$weightString, $matches);
        switch (count($matches)) {
            case 3:
                $units = $matches[2];
                //intentionally fall through
            case 2:
                $value = $matches[1];
                break;
            default:
                return 0;
                break;
        }
        switch ($units) {
            case 'k':
            case 'kg':
                return $this->kgToGrams($value);
                break;

            case 'l':
            case '#':
            case 'lb':
                return $this->lbToGrams($value);
            
            default:
                return 0;
                break;
        }
    }

    private function isBodyWeight(String $weightString){
        return 0 === strncmp($weightString, '@', 1);
    }

    private function kgToGrams($kg){
        return 1000 * $kg;
    }

    private function lbToGrams($lb){
        return $lb * 453.593;
    }
}
