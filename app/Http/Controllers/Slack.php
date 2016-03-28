<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Team;
use App\User;
use App\Movement;
use App\Lift;

class Slack extends Controller
{
    public function handle(Request $request){
        //TODO: move all of this into middleware that also validates input.
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
        $argc = $request->input('argc');
        $response = [
            'input' => $request->all()
        ];
        if($command = $request->input('command')){
            $response['command'] = $command;
            switch ($command) {
                case 'sex':
                    if($sex = $request->input('args')){
                        $sex = mb_substr($sex, 0, 1);
                        if('m' === $sex || 'f' === $sex){
                            $user->sex = $sex;
                            $user->save();
                            $response = "Sex for $user->slack_name is now $user->sex.";
                        }
                    }
                    break;
                
                case 'lift':
                    $movement = Movement::fromName($request->input('openingWord'));
                    $lift = new Lift([
                        'user_id'     => $user->id,
                        'movement_id' => $movement->id,
                        'grams'       => 420,
                        'bodygrams'   => 69,
                    ]);
                    $lift->save();
                    $response = "$user->slack_name has a new $movement->name of $lift->grams at $lift->bodygrams";
                    break;
                default:
                    # code...
                    break;
            }
        }
        if(is_array($response)){
            $response['team'] = $team;
            $response['user'] = $user;
            return json_encode($response,JSON_PRETTY_PRINT);
        }
        return $response . PHP_EOL;
        
    }
}
