<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://lift.app';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
    
    /**
     * Given an array, return a request with that array as input
     *
     * @param string $text value of text field in request
     * @return Illuminate\Http\Request Reequest object with minumum field required for middleware
     */
    protected function requestWithInput($input = []){
        $request = new Illuminate\Http\Request();
        $request->replace($input);
        return $request;
    }
    /**
     * Given a string, return a request with that string in the text field
     *
     * @param string $text value of text field in request
     * @return Illuminate\Http\Request Reequest object with minumum field required for middleware
     */
    protected function requestWithText($text = ''){
        return $this->requestWithInput([
            'text' => $text
        ]);
    }
}
