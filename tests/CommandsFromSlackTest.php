<?php

//use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Middleware\GetCommandFromSlack;
use Illuminate\Http\Request;

class CommandsFromSlackTest extends TestCase
{
    private function requestWithText($text = ''){
        $request = new Illuminate\Http\Request();
        $request->replace([
            'text' => $text
        ]);
        return $request;
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testShouldKnowLiftCommand()
    {
        $texts = [ //text from before $mw => text after mw
            'lift front squat 123lbx12@111 http://perk.ee/lift-test/'
                => 'front squat 123lbx12@111 http://perk.ee/lift-test/',
            'lift'
                => ''
        ];
        foreach ($texts as $before => $after) {
            $request = $this->requestWithText($before);
            $mw = new \App\Http\Middleware\GetCommandFromSlack;
            echo "testing '$before'\n => '$after'\n";
            $mw->handle($request,function($r) use ($after){
                $this->assertEquals('lift',$r->input('command'));
                $this->assertEquals($after,$r->input('text'));
            });
        }
    }
}

