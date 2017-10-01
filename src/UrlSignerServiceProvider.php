<?php

namespace Lab66\UrlSigner;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Lab66\UrlSigner\Middleware\ValidateSignature;
use Lab66\UrlSigner\Contracts\UrlSigner as UrlSignerContract;

class UrlSignerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->setupConfig($this->app);

        if ($this->app->runningInConsole())
        {
            $this->commands([
                Commands\UrlSignerGenerate::class
            ]);
        }
    }

    /**
     * Setup the config.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    protected function setupConfig(Application $app)
    {
        $source = realpath(__DIR__.'/../config/url-signer.php');
        $this->publishes([$source => config_path('url-signer.php')], 'url-signer');
        $this->mergeConfigFrom($source, 'url-signer');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/url-signer.php', 'url-signer');

        $config = config('url-signer');

        $this->app->singleton(UrlSignerContract::class, function () use ($config) {
            return new UrlSigner(
                $config['private_key'],
                $config['public_key'],
                $config['default_expiration'],
                $config['parameters']['expires'],
                $config['parameters']['signature']
            );
        });

        $this->app->alias(UrlSignerContract::class, 'url-signer');

        $router = $this->app[Router::class];
        $method = method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware';
        $router->$method('signed-url', ValidateSignature::class);
    }
}
