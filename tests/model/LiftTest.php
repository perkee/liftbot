<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Team;
use App\Lift;
use App\Movement;

class LiftTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $slack_domain    = 'test.lift.perk.ee';
        $slack_team_id   = 'testSlackTeamId';
        $slack_user_id   = 'testSlackUserId';
        $slack_user_name = 'testSlackUserName';
        
        $this->team = Team::firstOrCreate([
            'slack_id'=>$slack_team_id,
            'slack_domain'=>$slack_domain,
        ]);
        $this->team->save();
        $this->user = User::firstOrCreate([
            'team_id'    => $this->team->id,
            'slack_id'   => $slack_user_id,
            'slack_name' => $slack_user_name
        ]);
        $this->user->save();
        $this->mvmt = Movement::firstOrCreate([
            'name' => 'fake press',
            'hash' => 'fakepress'
        ]);
        $this->mvmt->save();
    }
    
    public function testShouldHaveDefaults()
    {
        $lift = new Lift([
            'grams' => 1000,
            'user_id' => $this->user->id,
            'movement_id' => $this->mvmt->id,
        ]);
        $lift->save();
        $this->assertEquals(1000,$lift->grams);
        $this->assertEquals($this->user->id,$lift->user->id);
        $this->assertEquals($this->mvmt->id,$lift->movement->id);
        $this->assertEquals(1,$lift->reps);

        $lift->units = 'k';
        $this->assertEquals('fake press: 1.0 kg × 1',"$lift");

        $lift->units = 'l';
        $this->assertEquals('fake press: 2.2 lb × 1',"$lift");

        $lift->bodygrams = 100000;
        $lift->save();

        $lift->units = 'k';
        $this->assertEquals('fake press: 1.0 kg × 1 @ 100.0 kg',"$lift");

        $lift->units = 'l';
        $this->assertEquals('fake press: 2.2 lb × 1 @ 220.5 lb',"$lift");


    }
}
