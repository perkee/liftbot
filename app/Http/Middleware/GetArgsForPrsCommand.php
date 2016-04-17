<?php

namespace App\Http\Middleware;

use Closure;
use \App\User;

class GetArgsForPrsCommand
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
        if (isset($input['text']) && isset($input['command']) && 'prs' == $input['command']) {
            $text = $input['text'];
            //only argument should be a username
            $queried_slack_name = preg_replace('/\s*@?\s*([-_a-zA-Z\d]+)/', '$1', $text);
            $queried_user = null;
            if ('' == $queried_slack_name) {
                //if no name, then give PRs for this user
                $queried_user = $request->user;
            } else {
                try {
                    $queried_user = \App\User::where('slack_name', $queried_slack_name)->firstOrFail();
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    die("$queried_slack_name doesn't even lift here");
                }
            }
            $units = $request->user->units;
            $seen = [];//movement_id of prs we have seen already.
            $lifts = $queried_user->lifts()->orderBy('grams', 'desc')->get()->reject(function ($lift) use (&$seen) {
                $movement_id = $lift->movement_id;
                $return = isset($seen[$movement_id]);
                $seen[$movement_id] = true;
                return $return;
            })->map(function ($lift) use ($units) {
                $lift->units = $units;
                return $lift->__toString();
            });
            $input['slack_name'] = $queried_user->slack_name;
            $input['user'] = $queried_user;
            $input['lifts'] = $lifts;
            $request->replace($input);
        }
        return $next($request);
    }
}
