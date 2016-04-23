<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Team;
use App\Lift;
use App\Movement;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $slack_domain    = 'test.lift.perk.ee';
        $slack_team_id   = 'testSlackTeamId';
        $slack_user_id   = 'testSlackUserId';
        $slack_user_name = 'testSlackUserName';
        if(!isset($this->team)){
            $this->team = Team::firstOrCreate([
                'slack_id'=>$slack_team_id,
                'slack_domain'=>$slack_domain,
            ]);
            $this->team->save();
        }
        if(!isset($this->user)){
            $this->user = User::firstOrCreate([
                'team_id'    => $this->team->id,
                'slack_id'   => $slack_user_id,
                'slack_name' => $slack_user_name
            ]);
            $this->user->save();
        }

        $this->mvmt = Movement::firstOrCreate([
            'name' => 'fake press',
            'hash' => 'fakepress'
        ]);
        $this->mvmt->save();
    }

    public function testShouldHaveTeam()
    {
        $this->assertInstanceOf(App\User::class,$this->user);
        $this->assertInstanceOf(App\Team::class,$this->user->team);
        $this->assertEquals($this->team->id,$this->user->team->id);
    }

    public function testShouldMakeLifts()
    {
        $this->assertEquals(0,$this->user->lifts->count());
        $lift = new Lift([
            'user_id'     => $this->user->id,
            'movement_id' => $this->mvmt->id,
            'grams'       => 100
        ]);
        $lift->save();
        $this->assertEquals($lift->user->id,$this->user->id);
        $this->assertEquals(1,$this->user->lifts()->count());
    }

    public function testShouldGetMaxes()
    {
        $this->assertEquals(0,$this->user->lifts->count());
        $weak = new Lift([
            'user_id'     => $this->user->id,
            'movement_id' => $this->mvmt->id,
            'grams'       => 100
        ]);
        $weak->save();
        $this->assertNotNull($weak->id);
        $strong = new Lift([
            'user_id'     => $this->user->id,
            'movement_id' => $this->mvmt->id,
            'grams'       => 200
        ]);
        $strong->save();
        $this->assertNotNull($strong->id);
        $this->assertEquals(2,$this->user->lifts()->count());
        $maxes = $this->user->maxLifts();

        $this->assertEquals(1,$maxes->count());

        $max = $maxes[0];

        $this->assertNotNull($max->id);
        $this->assertEquals($strong->id,$max->id);
        $this->assertNotEquals($weak->id,$max->id);
    }



    public function testShouldGetRepMaxes()
    {
        $this->assertEquals(0,$this->user->lifts->count());
        //two doubles
        $weak2 = new Lift([
            'user_id'     => $this->user->id,
            'movement_id' => $this->mvmt->id,
            'grams'       => 100,
            'reps'        => 2
        ]);
        $weak2->save();
        $this->assertNotNull($weak2->id);
        $strong2 = new Lift([
            'user_id'     => $this->user->id,
            'movement_id' => $this->mvmt->id,
            'grams'       => 200,
            'reps'        => 2
        ]);
        $strong2->save();
        $this->assertNotNull($strong2->id);
        //and two 5s
        $weak5 = new Lift([
            'user_id'     => $this->user->id,
            'movement_id' => $this->mvmt->id,
            'grams'       => 101,
            'reps'        => 5
        ]);
        $weak5->save();
        $this->assertNotNull($weak2->id);
        $strong5 = new Lift([
            'user_id'     => $this->user->id,
            'movement_id' => $this->mvmt->id,
            'grams'       => 201,
            'reps'        => 5
        ]);
        $strong5->save();
        $this->assertNotNull($strong5->id);

        $this->assertEquals(4,$this->user->lifts()->count());
        $maxes = $this->user->maxLifts();

        $this->assertEquals(2,$maxes->count());
        
        $max2 = $maxes[1];
        $max5 = $maxes[0];

        $this->assertNotNull($max2->id);
        $this->assertNotNull($max5->id);
        $this->assertEquals($strong2->id,$max2->id);
        $this->assertEquals($strong5->id,$max5->id);
    }
}
