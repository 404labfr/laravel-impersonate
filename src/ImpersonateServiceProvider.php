<?php

namespace Lab404\Impersonate;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\View\Compilers\BladeCompiler;
use Lab404\Impersonate\Guard\SessionGuard;
use Lab404\Impersonate\Middleware\ProtectFromImpersonation;
use Lab404\Impersonate\Services\ImpersonateManager;

/**
 * Class ServiceProvider
 *
 * @package Lab404\Impersonate
 */
class ImpersonateServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /** @var string $configName */
    protected $configName = 'laravel-impersonate';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();

        $this->app->bind(ImpersonateManager::class, ImpersonateManager::class);

        $this->app->singleton(ImpersonateManager::class, function ($app) {
            return new ImpersonateManager($app);
        });

        $this->app->alias(ImpersonateManager::class, 'impersonate');

        $this->registerRoutesMacro();
        $this->registerBladeDirectives();
        $this->registerMiddleware();
        $this->registerAuthDriver();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();

        // We want to remove data from storage on real login and logout
        Event::listen(Login::class, function ($event) {
            app('impersonate')->clear();
        });
        Event::listen(Logout::class, function ($event) {
            app('impersonate')->clear();
        });
    }

    /**
     * Register plugin blade directives.
     *
     * @param void
     * @return  void
     */
    protected function registerBladeDirectives()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('impersonating', function () {
                return "<?php if (is_impersonating()) : ?>";
            });

            $bladeCompiler->directive('endImpersonating', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('canImpersonate', function () {
                return "<?php if (can_impersonate()) : ?>";
            });

            $bladeCompiler->directive('endCanImpersonate', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('canBeImpersonated', function ($expression) {
                return "<?php if (can_be_impersonated({$expression})) : ?>";
            });

            $bladeCompiler->directive('endCanBeImpersonated', function () {
                return '<?php endif; ?>';
            });
        });
    }

    /**
     * Register routes macro.
     *
     * @param void
     * @return  void
     */
    protected function registerRoutesMacro()
    {
        $router = $this->app['router'];

        $router->macro('impersonate', function () use ($router) {
            $router->get('/impersonate/take/{id}/{guardName?}',
                '\Lab404\Impersonate\Controllers\ImpersonateController@take')->name('impersonate');
            $router->get('/impersonate/leave',
                '\Lab404\Impersonate\Controllers\ImpersonateController@leave')->name('impersonate.leave');
        });
    }

    /**
     * @param void
     * @return  void
     */
    protected function registerAuthDriver()
    {
        /** @var AuthManager $auth */
        $auth = $this->app['auth'];

        $auth->extend('session', function (Application $app, $name, array $config) use ($auth) {
            $provider = $auth->createUserProvider($config['provider']);

            $guard = new SessionGuard($name, $provider, $app['session.store']);

            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });
    }

    /**
     * Register plugin middleware.
     *
     * @param void
     * @return  void
     */
    public function registerMiddleware()
    {
        $this->app['router']->aliasMiddleware('impersonate.protect', ProtectFromImpersonation::class);
    }

    /**
     * Merge config file.
     *
     * @param void
     * @return  void
     */
    protected function mergeConfig()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->mergeConfigFrom($configPath, $this->configName);
    }

    /**
     * Publish config file.
     *
     * @param void
     * @return  void
     */
    protected function publishConfig()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->publishes([$configPath => config_path($this->configName . '.php')], 'impersonate');
    }
}