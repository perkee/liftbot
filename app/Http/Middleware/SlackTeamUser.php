<?php

namespace App\Http\Middleware;

use Closure;
use App\Team;
use App\User;

class SlackTeamUser
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
        $team = Team::firstOrNew([
            'slack_id' => $request->input('team_id')
        ]);
        $team->slack_domain = $request->input('team_domain');
        $team->save();

        $user = User::firstOrNew([
            'team_id'  => $team->id,
            'slack_id' => $request->input('user_id')
        ]);

        $user->slack_name = $request->input('user_name');

        $user->save();
        $request->user = $user;
        $request->team = $team;
        return $next($request);
    }
}
