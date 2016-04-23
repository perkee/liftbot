<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Middleware\GetArgsForPRsCommand;

use App\User;
use App\Team;
use App\Lift;
use App\Movement;

class ArgsForPRsTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * Many tests need a user and a team attached to the request
     */
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

    public function testShouldBypassOtherCommands()
    {
        //this command does nothing useful and should think about how useless it is.
        $inputs = [
            [],
            ['command' => ''],
            ['command' => 'prs'],
            ['command' => 'not lift']
        ];
        foreach ($inputs as $input) {
            //create a new one every time since it has instance vars
            $mw = new \App\Http\Middleware\GetArgsForPRsCommand;

            $beforeRequest = $this->requestWithInput($input);
            $mw->handle($beforeRequest, function ($afterRequest) use ($input) {
                $this->assertEquals($input, $afterRequest->all());
            });
        }
    }

    public function testShouldFailUnknownUser()
    {
        $this->setExpectedException(\Exception::class);

        $mw = new \App\Http\Middleware\GetArgsForPRsCommand;

        $request = $this->requestWithInput([
            'command' => 'prs',
            'text' => 'not a real person'
        ]);
        $mw->handle($request, function ($r){
            $this->fail('should have died already');
        });
    }

    public function testShouldGetOwnPrs()
    {

        $slack_domain    = 'test.lift.perk.ee';
        $slack_team_id   = 'testSlackTeamId';
        $slack_user_id   = 'testSlackUserId';
        $slack_user_name = 'testSlackUserName';

        $team = Team::firstOrCreate([
            'slack_id'=>$slack_team_id,
            'slack_domain'=>$slack_domain,
        ]);
        $team->save();
        $user = User::firstOrCreate([
            'team_id'    => $team->id,
            'slack_id'   => $slack_user_id,
            'slack_name' => $slack_user_name
        ]);
        $user->save();

        $mvmt = Movement::firstOrCreate([
            'name' => 'fake press',
            'hash' => 'fakepress'
        ]);
        $mvmt->save();

        $this->assertEquals(0,$user->lifts->count());
        //two doubles
        $weak2 = new Lift([
            'user_id'     => $user->id,
            'movement_id' => $mvmt->id,
            'grams'       => 100,
            'reps'        => 2
        ]);
        $weak2->save();
        $this->assertNotNull($weak2->id);
        $strong2 = new Lift([
            'user_id'     => $user->id,
            'movement_id' => $mvmt->id,
            'grams'       => 200,
            'reps'        => 2
        ]);
        $strong2->save();
        $this->assertNotNull($strong2->id);
        //and two 5s
        $weak5 = new Lift([
            'user_id'     => $user->id,
            'movement_id' => $mvmt->id,
            'grams'       => 101,
            'reps'        => 5
        ]);
        $weak5->save();
        $this->assertNotNull($weak2->id);
        $strong5 = new Lift([
            'user_id'     => $user->id,
            'movement_id' => $mvmt->id,
            'grams'       => 201,
            'reps'        => 5
        ]);
        $strong5->save();
        $this->assertNotNull($strong5->id);

        $this->assertEquals(4,$user->lifts()->count());

        $maxes = $user->maxLifts();

        $this->assertEquals(2,$maxes->count());

        $mw = new \App\Http\Middleware\GetArgsForPRsCommand;

        $request = $this->requestWithInput([
            'command' => 'prs',
            'text' => '',
        ]);
        $request->user = $user; //to get units for output;

        $test = $this;
        $mw->handle($request, function ($r) use ($test, $maxes){

            $test->assertNotNull($r);
            $lifts = $r->input('lifts');
            $test->assertNotNull($lifts);
            $test->assertEquals(2,count($lifts));
            $test->assertEquals($maxes[0]->id,$lifts[0]->id);
            $test->assertEquals($maxes[1]->id,$lifts[1]->id);
        });
    }



    public function testShouldGetOtherPrs()
    {

        $slack_domain    = 'test.lift.perk.ee';
        $slack_team_id   = 'testSlackTeamId';
        $slack_user_id   = 'testSlackUserId';
        $slack_user_name = 'testSlackUserName';

        $team = Team::firstOrCreate([
            'slack_id'=>$slack_team_id,
            'slack_domain'=>$slack_domain,
        ]);
        $team->save();
        $user = User::firstOrCreate([
            'team_id'    => $team->id,
            'slack_id'   => $slack_user_id,
            'slack_name' => $slack_user_name
        ]);
        $user->save();

        $mvmt = Movement::firstOrCreate([
            'name' => 'fake press',
            'hash' => 'fakepress'
        ]);
        $mvmt->save();

        $this->assertEquals(0,$user->lifts->count());
        //two doubles
        $weak2 = new Lift([
            'user_id'     => $user->id,
            'movement_id' => $mvmt->id,
            'grams'       => 100,
            'reps'        => 2
        ]);
        $weak2->save();
        $this->assertNotNull($weak2->id);
        $strong2 = new Lift([
            'user_id'     => $user->id,
            'movement_id' => $mvmt->id,
            'grams'       => 200,
            'reps'        => 2
        ]);
        $strong2->save();
        $this->assertNotNull($strong2->id);
        //and two 5s
        $weak5 = new Lift([
            'user_id'     => $user->id,
            'movement_id' => $mvmt->id,
            'grams'       => 101,
            'reps'        => 5
        ]);
        $weak5->save();
        $this->assertNotNull($weak2->id);
        $strong5 = new Lift([
            'user_id'     => $user->id,
            'movement_id' => $mvmt->id,
            'grams'       => 201,
            'reps'        => 5
        ]);
        $strong5->save();
        $this->assertNotNull($strong5->id);

        $this->assertEquals(4,$user->lifts()->count());

        $maxes = $user->maxLifts();

        $this->assertEquals(2,$maxes->count());

        $mw = new \App\Http\Middleware\GetArgsForPRsCommand;

        $request = $this->requestWithInput([
            'command' => 'prs',
            'text' => $slack_user_name,
        ]);
        $request->user = new User; //to get units for output;

        $test = $this;
        $mw->handle($request, function ($r) use ($test, $maxes){

            $test->assertNotNull($r);
            $lifts = $r->input('lifts');
            $test->assertNotNull($lifts);
            $test->assertEquals(2,count($lifts));
            $test->assertEquals($maxes[0]->id,$lifts[0]->id);
            $test->assertEquals($maxes[1]->id,$lifts[1]->id);
        });
    }

}
