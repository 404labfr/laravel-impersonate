<?php

namespace Lab404\Impersonate\Services;

use Illuminate\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Lab404\Impersonate\Events\TakeImpersonation;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Exceptions\CannotImpersonateException;
use Lab404\Impersonate\Exceptions\CannotBeImpersonatedException;

class ImpersonateManager
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var boolean
     */
    private $isGuarded = true;

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
     * @param   int $id
     * @return  Model
     */
    public function findUserById($id)
    {
        $model = $this->app['config']->get('auth.providers.users.model');

        $user = call_user_func([
            $model,
            'findOrFail'
        ], $id);

        return $user;
    }

    /**
     * @return bool
     */
    public function isImpersonating()
    {
        return session()->has($this->getSessionKey());
    }

    /**
     * @param   void
     * @return  int|null
     */
    public function getImpersonatorId()
    {
        return session($this->getSessionKey(), null);
    }

    /**
     * @param Model $from
     * @param Model $to
     * @return bool
     */
    public function forceTake($from, $to)
    {
        $this->isGuarded = false;
        $return = $this->take($from, $to);
        $this->isGuarded = true;

        return $return;
    }

    /**
     * @param Model $from
     * @param Model $to
     * @return bool
     */
    public function take($from, $to)
    {
        if ($this->isGuarded) {
            if (method_exists($from, 'canImpersonate')) {
                if (!$from->canImpersonate($to)) {
                    throw new CannotImpersonateException($from, $to, "The user {$from->id} cannot impersonate the user {$to->id}.");
                }
            }

            if (method_exists($to, 'canBeImpersonated')) {
                if (!$to->canBeImpersonated($from)) {
                    throw new CannotBeImpersonatedException($from, $to, "The user {$to->id} cannot be impersonated by the user {$from->id}.");
                }
            }
        }

        try {
            session()->put($this->getSessionKey(), $from->getKey());

            $this->app['auth']->quietLogout();
            $this->app['auth']->quietLogin($to);

        } catch (\Exception $e) {
            unset($e);
            return false;
        }

        $this->app['events']->fire(new TakeImpersonation($from, $to));

        return true;
    }

    /**
     * @return  bool
     */
    public function leave()
    {
        try {
            $impersonated = $this->app['auth']->user();
            $impersonator = $this->findUserById($this->getImpersonatorId());

            $this->app['auth']->quietLogout();
            $this->app['auth']->quietLogin($impersonator);

            $this->clear();

        } catch (\Exception $e) {
            unset($e);
            return false;
        }

        $this->app['events']->fire(new LeaveImpersonation($impersonator, $impersonated));

        return true;
    }

    /**
     * @return void
     */
    public function clear()
    {
        session()->forget($this->getSessionKey());
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return config('laravel-impersonate.session_key');
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
}
