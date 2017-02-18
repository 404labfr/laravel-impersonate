<?php

namespace Lab404\Impersonate\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;

class ImpersonateManager
{
    /**
     * @var Application
     */
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
    public function take($from, $to)
    {
        try
        {
            $impersonator = $this->app['auth']->user();
            $impersonated = $to;

            session()->put(config('laravel-impersonate.session_key'), $from->id);

            $this->app['auth']->logout();
            $this->app['auth']->login($to);

        } catch (\Exception $e)
        {
            unset($e);
            return false;
        }

        $this->app['events']->fire(new TakeImpersonation($impersonator, $impersonated));

        return true;
    }

    /**
     * @return  bool
     */
    public function leave()
    {
        try
        {
            $impersonated = $this->app['auth']->user();

            $this->app['auth']->logout();
            $this->app['auth']->loginUsingId($this->getImpersonatorId());

            $impersonator = $this->app['auth']->user();

            $this->clear();

        } catch (\Exception $e)
        {
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
        return config('laravel-impersonate.take_redirect_to');
    }

    /**
     * @return  string
     */
    public function getLeaveRedirectTo()
    {
        return config('laravel-impersonate.leave_redirect_to');
    }
}
