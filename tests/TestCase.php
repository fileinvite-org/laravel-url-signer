<?php

namespace Lab66\UrlSigner\Laravel\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use Lab66\UrlSigner\Laravel\UrlSignerServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @var string
     */
    protected $hostName;

    public function setUp()
    {
        $this->setApplicationKey();

        parent::setUp();

        $this->hostName = $this->app['config']->get('app.url');

        $this->registerDefaultRoute();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            UrlSignerServiceProvider::class,
        ];
    }

    protected function setApplicationKey()
    {
        putenv('APP_KEY=mysecretkey');
    }

    protected function registerDefaultRoute()
    {
        $this->app['router']->get('protected-route', ['middleware' => 'signedurl', function () {
            return 'Hello world!';
        }]);
    }
}
