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
                    throw new \Exception("$queried_slack_name doesn't even lift here");
                }
            }
            $lifts = $queried_user->maxLifts();
            $input['slack_name'] = $queried_user->slack_name;
            $input['user'] = $queried_user;
            $input['lifts'] = $lifts;
            $request->replace($input);
        }
        return $next($request);
    }
}
