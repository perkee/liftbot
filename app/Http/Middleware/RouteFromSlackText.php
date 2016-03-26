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

                //collapse spaces between digits and units 
                $filtered = preg_replace('/(@*) *(\d+) *(kg|lb|#)/', '$1$2$3', $filtered);
                
                //weights and bodyweights ought to be separated by slashes
                $filtered = str_replace(' ', $delim, $filtered);


                $input['openingWord'] = $openingWord;
                $input['args'] = $args;
                $input['command'] = $command;
                $input['filtered'] = $filtered;

            }
        }
        $request->replace($input);

        return $next($request);
    }
}
