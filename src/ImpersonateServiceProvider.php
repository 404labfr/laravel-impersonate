<?php

namespace Lab404\Impersonate;

use Illuminate\Routing\Router;
use Lab404\Impersonate\Services\ImpersonateManager;

/**
 * Class ServiceProvider
 *
 * @package Lab404\Impersonate
 */
class ImpersonateServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = false;

    /**
     * @var string
     */
    protected $configName = 'laravel-impersonate';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->mergeConfigFrom($configPath, $this->configName);

        $this->app->bind(ImpersonateManager::class, ImpersonateManager::class);

        $this->app->singleton(ImpersonateManager::class, function ($app)
        {
            return new ImpersonateManager($app);
        });

        $router = $this->app['router'];
        $router->group([
            'prefix'     => '/impersonate',
            'middleware' => ['web'],
            'as'         => 'impersonate.',
            'namespace'  => 'Lab404\\Impersonate\\Controllers'
        ], function (Router $router)
        {
            $router->get('/take/{id}', 'ImpersonateController@take')->name('take');
            $router->get('/leave', 'ImpersonateController@leave')->name('leave');
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->publishes([$configPath => config_path($this->configName . '.php')], 'config');
    }
}