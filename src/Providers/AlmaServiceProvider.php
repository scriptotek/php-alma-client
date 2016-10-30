<?php

namespace Scriptotek\Alma\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Sru\Client as SruClient;

class AlmaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('alma.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php',
            'alma'
        );
        $this->app->singleton('alma', function ($app) {
            $alma = new AlmaClient($app['config']->get('alma.iz.key'), $app['config']->get('alma.region'));
            $alma->nz->setKey($app['config']->get('alma.nz.key'));

            $sru_url = $app['config']->get('alma.iz.sru');
            if ($sru_url) {
                $alma->setSruClient(new SruClient(
                    $sru_url,
                    ['version' => '1.2', 'schema' => 'marcxml']
                ));
            }
            $sru_url = $app['config']->get('alma.nz.sru');
            if ($sru_url) {
                $alma->nz->setSruClient(new SruClient(
                    $sru_url,
                    ['version' => '1.2', 'schema' => 'marcxml']
                ));
            }

            return $alma;
        });

        $app->alias('alma', AlmaClient::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['alma'];
    }
}
