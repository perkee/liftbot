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
        $user = $request->user;
        $team = $request->team;

        $argc = $request->input('argc');
        $response = [
            'req'   => $request,
            'input' => $request->all()
        ];
        if($command = $request->input('command')){
            $response['command'] = $command;
            switch ($command) {
                case 'prs':
                    return $this->handlePrs($request);
                case 'sex':
                    if($sex = $request->input('args')){
                        $sex = mb_substr($sex, 0, 1);
                        if('m' === $sex || 'f' === $sex){
                            $user->sex = $sex;
                            $user->save();
                            $response['nice'] = "Sex for $user->slack_name is now $user->sex.";
                        }
                    }
                    break;
                
                case 'lift':
                    $movement = \App\Movement::firstOrCreateFromName($request->input('movementName'));
                    $lift = new Lift([
                        'user_id'     => $user->id,
                        'movement_id' => $movement->id,
                        'grams'       => $request->input('grams'),
                        'bodygrams'   => $request->input('bodyGrams'),
                        'url'         => $request->input('url')
                    ]);
                    $lift->save();
                    $response['movement'] = $movement;
                    $response['lift'] = $lift;
                    $response['nice'] = "$user->slack_name has a new $movement->name of $lift->grams at $lift->bodygrams";
                    break;

                case 'stats':
                    $args = explode(' ',$request->input('args'),2);
                    $query_user_slack_name = $args[0];
                    $movement_name = $args[1];
                    $movement = \App\Movement::whereName($movement_name);
                    $query_user = null;
                    $lift = \App\Lift::where('movement_id',$movement);
                default:
                    $response['nice'] = "Unknown command: $command";
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

    public function handlePrs($request){
        $lifts = $request->input('lifts');
        $slack_name = $request->input('slack_name');
        if(!$lifts->isEmpty()){
            $join = "\n\t";
            return "PRs for ${slack_name}:${join}" . $lifts->implode($join);
        }
        else{
            return "${slack_name} should get some PRs";
        }
    }
}
