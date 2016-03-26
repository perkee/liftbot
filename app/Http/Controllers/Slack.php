<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Team;
use App\User;

class Slack extends Controller
{
    public function handle(Request $request){
        $input = $request->input();
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

        $response = [
            'input' => $request->all(),
            'team' => $team,
            'user' => $user,
        ];

        return json_encode($response,JSON_PRETTY_PRINT);
        
        return "team: $team->id\nuser: $user->id" . PHP_EOL;
    }
}
