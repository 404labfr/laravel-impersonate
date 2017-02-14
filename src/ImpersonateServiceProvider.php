<?php

namespace Lab404\Impersonate;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
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

        $this->app->alias(ImpersonateManager::class, 'impersonate');

        $router = $this->app['router'];
        $router->group([
            'prefix'     => '/impersonate',
            'middleware' => ['web'],
            'namespace'  => 'Lab404\\Impersonate\\Controllers'
        ], function (Router $router)
        {
            $router->get('/take/{id}', 'ImpersonateController@take')->name('impersonate');
            $router->get('/leave', 'ImpersonateController@leave')->name('impersonate.leave');
        });

        $this->registerBladeDirectives();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->publishes([$configPath => config_path($this->configName . '.php')], 'impersonate');
    }

    /**
     * Register plugin blade directives.
     *
     * @param   void
     * @return  void`
     */
    protected function registerBladeDirectives()
    {
        Blade::directive('impersonating', function($expression) {
            return '<?php if (app()["auth"]->check() && app()["auth"]->user()->isImpersonated()): ?>';
        });

        Blade::directive('endImpersonating', function($expression) {
            return '<?php endif; ?>';
        });

        Blade::directive('canImpersonate', function($expression) {
            return '<?php if (app()["auth"]->check() && app()["auth"]->user()->canImpersonate()): ?>';
        });

        Blade::directive('endCanImpersonate', function($expression) {
            return '<?php endif; ?>';
        });
    }
}