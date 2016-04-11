<?php

namespace App\Http\Middleware;

use Closure;
use \Illuminate\Http\Request;

class GetCommandFromSlack
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(\Illuminate\Http\Request $request, Closure $next)
    {
        $input = $request->all();
        if( isset($input['text']) && $text = $input['text']){
            $text = trim($text);
            $text = explode(' ',$text,2);
            $count = count($text);
            if($count > 0){
                $input['command'] = $text[0];
                if($count > 1){
                    $input['text'] = ltrim($text[1]); //already trimmed right side
                }
                else{
                    $input['text'] = '';
                }
            }
            $request->replace($input);
        }
        return $next($request);
    }
}
