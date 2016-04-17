<?php

namespace App\Http\Middleware;

use \Illuminate\Http\Request;
use Closure;
use Log;

class GetArgsForLiftCommand
{
    /**
     * text from request input containing all params for lift command
     * We chip away at it as we parse the text.
     * @var string
     */
    public $text;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($input = $this->liftRequestInput($request)) {

            //reusable array for running preg_match
            $matches = [];
            //patterns we need to use twice to get values then remove from input
            $regex = (object)[
                'weight' => '/(?<![\w.\/])\s*(@?)\s*(\d+)\s*(kg?|lb?|#)?s?\s*/',
                'url'    => '/https?:\/\/[-.a-zA-Z\/\d?=_]+/'
            ];
            //get number of reps
            $reps = $this->getReps($this->text);

            //get weights

            preg_match_all($regex->weight, $this->text, $matches);

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
            $this->text = preg_replace($regex->weight, '', $this->text);

            //get URL
            preg_match($regex->url, $this->text, $matches);
            if (count($matches) > 0) {
                $input['url'] = $matches[0];
                $this->text = preg_replace($regex->url, '', $this->text);
            }

            //finally pull the movement name out
            $movementName = preg_replace('/^ *([- a-zA-Z_]+)([-a-zA-Z_]+).*/', '$1$2', $this->text);
            
            //Drop movement name from the start of the command
            $this->text = preg_replace('/^[^\d]*/', '', $this->text);

            $input['movementName'] = $movementName;
            $input['reps'] = $reps;
            $input['text'] = $this->text;
            if ($this->isValid($input)) {
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

    /**
     * Given a string command, return the nubmer of reps in the string
     * and remove the reps from the input string
     *
     * @param  string $text command string from Slack
     * @return int number of reps
     */
    public function getReps(&$text)
    {
        //reusable array for running preg_match
        $matches = [];
        //patterns we need to use twice to get values then remove from input
        $regex = '/[x*]\s*\d+/i';
        //get number of reps
        $reps = 1;
        preg_match($regex, $text, $matches);
        if (isset($matches[0])) {
            $reps = + substr($matches[0], 1);
            $text = preg_replace($regex, '', $text);
        }
        return $reps;
    }

    /**
     * returns array of input data from request only if request is valid,
     * otherwise returns false;
     * @param  \Illuminate\Http\Request
     * @return mixed input array if valid, false if not
     */
    public function liftRequestInput(\Illuminate\Http\Request $request)
    {
        $return = false;
        if($request->has('command')){
            if('lift' === $request->input('command')){
                if ($request->has('text')){
                    //this might be a good command
                    //but we should furthermore see if the text is somewhat worthwhile
                    //and as a side effect set this object's instance copy of it
                    $input = $request->all();
                    $this->text = trim($input['text']);
                    if (!$this->text) {
                        throw new \Exception("Lift needs a movement and weight at least, got '$this->text'");
                    }
                    $return = $input;
                }
                else{
                    throw new \Exception('Lift command requires a movement and weight at least, got no arguments.');
                }
            }
        }
        //else: this is not a lift command so we pass it by.
        return $return;
    }

    /**
     * @param  array $input input from $request->all() modified to include fields for lift command
     * @return boolean whether or not minimum required fields are set in input
     */
    protected function isValid(&$input = [])
    {
        $input['movementName'] = trim($input['movementName']);
        if(empty($input['movementName'])){
            unset($input['movementName']);
        }
        return isset(
            $input['movementName'],
            $input['reps'],
            $input['grams']
        ) &&
        is_string($input['movementName']) &&
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
