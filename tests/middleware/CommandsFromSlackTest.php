<?php

//use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Middleware\GetCommandFromSlack;
use Illuminate\Http\Request;

class CommandsFromSlackTest extends TestCase
{
    /**
     * Given a string, return a request with that string in the text field
     *
     * @param string $text value of text field in request
     * @return Illuminate\Http\Request Reequest object with minumum field required for middleware
     */
    private function requestWithText($text = ''){
        $request = new Illuminate\Http\Request();
        $request->replace([
            'text' => $text
        ]);
        return $request;
    }
    /**
     * Test that the lift command is set correctly for typical lift commands.
     *
     * @return void
     */
    public function testShouldKnowLiftCommand()
    {
        $texts = [ //text from before $mw => text after mw
            'lift front squat 123lbx12@111 http://perk.ee/lift-test/'
                => 'front squat 123lbx12@111 http://perk.ee/lift-test/',
            'lift' => '',
            ' lift ' => '',
            ' lift'  => '',
            'LIFT '  => ''
        ];
        foreach ($texts as $before => $after) {
            $request = $this->requestWithText($before);
            $mw = new \App\Http\Middleware\GetCommandFromSlack;
            $mw->handle($request,function($r) use ($after){
                $this->assertEquals('lift',$r->input('command'));
                $this->assertEquals($after,$r->input('text'));
            });
        }
    }

    /**
     * Test that the PRs command is set correctly for typical lift commands.
     *
     * @return void
     */
    public function testShouldKnowPRsCommand()
    {
        $texts = [ //text from before $mw => text after mw
            '  PRs   @mr-ms-nikhil-thomas  '
                => '@mr-ms-nikhil-thomas',
            'prs' => '',
            ' PRS ' => '',
            ' prS'  => '',
            'prs    @perkee '  => '@perkee'
        ];
        foreach ($texts as $before => $after) {
            $request = $this->requestWithText($before);
            $mw = new \App\Http\Middleware\GetCommandFromSlack;
            $mw->handle($request,function($r) use ($after){
                $this->assertEquals('prs',$r->input('command'));
                $this->assertEquals($after,$r->input('text'));
            });
        }
    }
}

