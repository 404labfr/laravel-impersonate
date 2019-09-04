<?php

namespace Lab404\Impersonate\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;

class ImpersonateManager
{
    const REMEMBER_PREFIX = 'remember_web';

    /** @var Application $app */
    private $app;

    /**
     * UserFinder constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param int $id
     * @return  Model
     * @throws Exception
     */
    public function findUserById($id, $guardName = null)
    {
        if (empty($guardName)) {
            $guardName = $this->app['config']->get('auth.default.guard', 'web');
        }

        $userProvider = $this->app['config']->get("auth.guards.$guardName.provider");
        $model = $this->app['config']->get("auth.providers.$userProvider.model");

        if (!$model) {
            throw new Exception("Auth guard \"$guardName\" does not exist.");
        }

        /** @var Model $modelInstance */
        $modelInstance = call_user_func([
            $model,
            'findOrFail'
        ], $id);

        return $modelInstance;
    }

    /**
     * @return bool
     */
    public function isImpersonating()
    {
        return session()->has($this->getSessionKey());
    }

    /**
     * @param void
     * @return  int|null
     */
    public function getImpersonatorId()
    {
        return session($this->getSessionKey(), null);
    }

    /**
     * Get impersonator model
     *
     * @return  Model
     */
    public function getImpersonator()
    {
        $id = session($this->getSessionKey(), null);

        return is_null($id) ? null : $this->findUserById($id);
    }
  
    /**
     * @return string|null
     */
    public function getImpersonatorGuardName()
    {
        return session($this->getSessionGuard(), null);
    }

    /**
     * @return string|null
     */
    public function getImpersonatorGuardUsingName()
    {
        return session($this->getSessionGuardUsing(), null);
    }

    /**
     * @param Model       $from
     * @param Model       $to
     * @param string|null $guardName
     * @return bool
     */
    public function take($from, $to, $guardName = null)
    {
        $this->saveAuthCookieInSession();

        try {
            $currentGuard = $this->getCurrentAuthGuardName();
            session()->put($this->getSessionKey(), $from->getKey());
            session()->put($this->getSessionGuard(), $currentGuard);
            session()->put($this->getSessionGuardUsing(), $guardName);

            $this->app['auth']->guard($currentGuard)->quietLogout();
            $this->app['auth']->guard($guardName)->quietLogin($to);

        } catch (\Exception $e) {
            unset($e);
            return false;
        }

        $this->app['events']->dispatch(new TakeImpersonation($from, $to));

        return true;
    }

    /**
     * @return  bool
     */
    public function leave()
    {
        try {
            $impersonated = $this->app['auth']->guard($this->getImpersonatorGuardUsingName())->user();
            $impersonator = $this->findUserById($this->getImpersonatorId(), $this->getImpersonatorGuardName());

            $this->app['auth']->guard($this->getCurrentAuthGuardName())->quietLogout();
            $this->app['auth']->guard($this->getImpersonatorGuardName())->quietLogin($impersonator);

            $this->extractAuthCookieFromSession();

            $this->clear();

        } catch (\Exception $e) {
            unset($e);
            return false;
        }

        $this->app['events']->dispatch(new LeaveImpersonation($impersonator, $impersonated));

        return true;
    }

    /**
     * @return void
     */
    public function clear()
    {
        session()->forget($this->getSessionKey());
        session()->forget($this->getSessionGuard());
        session()->forget($this->getSessionGuardUsing());
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return config('laravel-impersonate.session_key');
    }

    /**
     * @return string
     */
    public function getSessionGuard()
    {
        return config('laravel-impersonate.session_guard');
    }

    /**
     * @return string
     */
    public function getSessionGuardUsing()
    {
        return config('laravel-impersonate.session_guard_using');
    }

    /**
     * @return string
     */
    public function getDefaultSessionGuard()
    {
        return config('laravel-impersonate.default_impersonator_guard');
    }

    /**
     * @return  string
     */
    public function getTakeRedirectTo()
    {
        try {
            $uri = route(config('laravel-impersonate.take_redirect_to'));
        } catch (\InvalidArgumentException $e) {
            $uri = config('laravel-impersonate.take_redirect_to');
        }

        return $uri;
    }

    /**
     * @return  string
     */
    public function getLeaveRedirectTo()
    {
        try {
            $uri = route(config('laravel-impersonate.leave_redirect_to'));
        } catch (\InvalidArgumentException $e) {
            $uri = config('laravel-impersonate.leave_redirect_to');
        }

        return $uri;
    }

    /**
     * @return array
     */
    public function getCurrentAuthGuardName()
    {
        $guards = array_keys(config('auth.guards'));
        foreach ($guards as $guard) {
            if ($this->app['auth']->guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }

    /**
     * @return void
     */
    protected function saveAuthCookieInSession()
    {
        $cookie = $this->findByKeyInArray($this->app['request']->cookies->all(), static::REMEMBER_PREFIX);
        $key = $cookie->keys()->first();
        $val = $cookie->values()->first();

        if (!$key || !$val) {
            return;
        }

        session()->put(static::REMEMBER_PREFIX, [
            $key,
            $val,
        ]);
    }

    /**
     * @return void
     */
    protected function extractAuthCookieFromSession()
    {
        if (!$session = $this->findByKeyInArray(session()->all(), static::REMEMBER_PREFIX)->first()) {
            return;
        }

        $this->app['cookie']->queue($session[0], $session[1]);
        session()->forget($session);
    }

    /**
     * @param array  $values
     * @param string $search
     * @return \Illuminate\Support\Collection
     */
    protected function findByKeyInArray(array $values, string $search)
    {
        return collect($values ?? session()->all())
            ->filter(function ($val, $key) use ($search) {
                return strpos($key, $search) !== false;
            });
    }
}
