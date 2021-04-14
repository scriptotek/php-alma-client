<?php

namespace Scriptotek\Alma\Laravel;

use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Zones;
use Scriptotek\Sru\Client as SruClient;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/alma.php' => config_path('alma.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/alma.php',
            'alma'
        );

        $this->app->singleton(AlmaClient::class, function ($app) {

            // Create Alma client
            $alma = new AlmaClient(
                $app['config']->get('alma.iz.key'),
                $app['config']->get('alma.region'),
                Zones::INSTITUTION,
                isset($app[HttpClientInterface::class]) ? $app[HttpClientInterface::class] : null,
                isset($app[RequestFactoryInterface::class]) ? $app[RequestFactoryInterface::class] : null,
                isset($app[UriFactoryInterface::class]) ? $app[UriFactoryInterface::class] : null,
                $app['config']->get('alma.baseUrl'),
                $app['config']->get('alma.extraHeaders', [])
            );

            // Set network zone key, if any
            $alma->nz->setKey($app['config']->get('alma.nz.key'));

            // Optionally, attach SRU client for institution zone
            if ($app['config']->get('alma.iz.sru')) {
                $alma->setSruClient(new SruClient(
                    $app['config']->get('alma.iz.sru'),
                    ['version' => '1.2', 'schema' => 'marcxml']
                ));
            }

            // Optionally, attach SRU client for network zone
            if ($app['config']->get('alma.nz.sru')) {
                $alma->nz->setSruClient(new SruClient(
                    $app['config']->get('alma.nz.sru'),
                    ['version' => '1.2', 'schema' => 'marcxml']
                ));
            }

            return $alma;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [AlmaClient::class];
    }
}
