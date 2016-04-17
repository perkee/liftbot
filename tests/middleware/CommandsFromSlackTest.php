<?php

use App\Http\Middleware\GetCommandFromSlack;

class CommandsFromSlackTest extends TestCase
{
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
            $mw->handle($request, function ($r) use ($after) {
                $this->assertEquals('lift', $r->input('command'));
                $this->assertEquals($after, $r->input('text'));
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
            $mw->handle($request, function ($r) use ($after) {
                $this->assertEquals('prs', $r->input('command'));
                $this->assertEquals($after, $r->input('text'));
            });
        }
    }
}
