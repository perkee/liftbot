<?php

use App\Http\Middleware\GetArgsForLiftCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Team;
use App\User;

class ArgsForLiftTest extends TestCase
{
    //use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->team = new App\Team([
            'slack_id' => 'testing'
        ]);
        $this->user = new App\User([
            'team_id' => $this->team->id,
            'slack_id'=> 'testing'
        ]);
    }
    
    /**
     * Should parse reps out of command.
     *
     * @return void
     */
    public function testShouldKnowReps()
    {
        $texts = [ //text from before $mw => text after mw
            'front squat 123lb x12 @ 111lb http://perk.ee/lift-test/'
                => 12
        ];
        foreach ($texts as $text => $reps) {
            $request = $this->requestWithInput([
               'text' => $text,
               'command' => 'lift'
            ]);
            $request->team = $this->team;
            $request->user = $this->user;
            $mw = new \App\Http\Middleware\GetArgsForLiftCommand;
            $mw->handle($request,function($r) use ($reps){
                $this->assertEquals($reps,$r->input('reps'));
            });
        }
    }
}
