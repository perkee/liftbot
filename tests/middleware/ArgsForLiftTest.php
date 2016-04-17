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

    public function testShouldExceptOnNoText()
    {
        $this->setExpectedException('Exception');
        $mw = new \App\Http\Middleware\GetArgsForLiftCommand;
        $mw->handle($this->requestWithInput(['command'=>'lift']), function ($request) {
            $this->fail('Should not reach this callback' . json_encode(['r'=>$request,'i'=>$request->all()], JSON_PRETTY_PRINT));
        });
    }

    public function testShouldExceptOnWhitespaceText()
    {
        $this->setExpectedException(\Exception::class);
        $mw = new \App\Http\Middleware\GetArgsForLiftCommand;
        $mw->handle($this->requestWithInput(['command'=>'lift','text' => '   ']), function ($request) {
            $this->fail('Should not reach this callback' . json_encode(['r'=>$request,'i'=>$request->all()], JSON_PRETTY_PRINT));
        });
    }

    /**
     * Should die when there is no weight for lift
     *
     * @return void
     */
    public function testShouldExceptWithoutWeight()
    {
        $text = 'front squat x12 @ 111kg http://perk.ee/lift-test/';// no lift weight

        $this->setExpectedException(\Exception::class);
        $request = $this->requestWithInput([
           'text' => $text,
           'command' => 'lift'
        ]);
        $mw = new \App\Http\Middleware\GetArgsForLiftCommand;
        $mw->handle($request, function ($request) {
            $that->fail('Should not reach this callback' . json_encode(['r'=>$request,'i'=>$request->all()], JSON_PRETTY_PRINT));
        });
    }

    /**
     * Should die when there is no movement name for lift
     *
     * @return void
     */
    public function testShouldExceptWithoutMovementName()
    {
        $text = 'x15 145kg @111lbhttps://www.instagram.com/p/BD0daiMvI1w/?taken-by=perk.ee'; //no movement name

        $this->setExpectedException(\Exception::class);
        $request = $this->requestWithInput([
           'text' => $text,
           'command' => 'lift'
        ]);
        $mw = new \App\Http\Middleware\GetArgsForLiftCommand;
        $mw->handle($request, function ($request) {
            $that->fail('Should not reach this callback' . json_encode(['r'=>$request,'i'=>$request->all()], JSON_PRETTY_PRINT));
        });
    }

    /**
     * Should parse reps out of a command
     *
     * @return void
     */
    public function testShouldKnowReps()
    {

        $texts = [ //text from before $mw => reps after parsing from text
            'front squat 123lb x12 @ 111kg http://perk.ee/lift-test/' => [
                'reps' => 12,
                'text' => 'front squat 123lb  @ 111kg http://perk.ee/lift-test/'

            ],
            'front squatx15 145kg @111lbhttps://www.instagram.com/p/BD0daiMvI1w/?taken-by=perk.ee' => [
                'reps' => 15,
                'text' => 'front squat 145kg @111lbhttps://www.instagram.com/p/BD0daiMvI1w/?taken-by=perk.ee'
            ],
        ];
        foreach ($texts as $text => $args) {
            $mw = new \App\Http\Middleware\GetArgsForLiftCommand;
            $mw->text = $text;
            $this->assertEquals($args['reps'], $mw->getReps($mw->text));
            $this->assertEquals($args['text'], $mw->text);
        }
    }
    
    /**
     * Should parse args out of command.
     *
     * @return void
     */
    public function testShouldKnowArgs()
    {
        $texts = [ //text from before $mw => reps after parsing from text
            'front squat 123lb x12 @ 111kg http://perk.ee/lift-test/' => [
                'reps'      => 12,
                'grams'     => 123 * 453.593,
                'bodyGrams' => 111 * 1000,
                'url'       => 'http://perk.ee/lift-test/'

            ],
            'front squatx15 145kg @111lbhttps://www.instagram.com/p/BD0daiMvI1w/?taken-by=perk.ee' => [
                'reps'      => 15,
                'grams'     => 145 * 1000,
                'bodyGrams' => 111 * 453.593,
                'url'       => 'https://www.instagram.com/p/BD0daiMvI1w/?taken-by=perk.ee'

            ],
        ];
        foreach ($texts as $text => $args) {
            $request = $this->requestWithInput([
               'text' => $text,
               'command' => 'lift'
            ]);
            $request->team = $this->team;
            $request->user = $this->user;
            $mw = new \App\Http\Middleware\GetArgsForLiftCommand;
            //$mw->text = $text;
            $mw->handle($request, function ($r) use ($args) {
                foreach ($args as $key => $value) {
                    if (is_numeric($value)) {
                        $this->assertEquals($value, $r->input($key), $key, 0.001);
                    } else {
                        $this->assertEquals($value, $r->input($key), $key);
                    }
                }
            });
        }
    }
}
