<?php

namespace Lab404\Impersonate\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;
use Lab404\Impersonate\Services\ImpersonateManager;
use Illuminate\Contracts\Auth\Factory as Auth;
//\Illuminate\Auth\Middleware\Authenticate::class,

class ProtectAuthenticateImpersonation
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param   \Illuminate\Http\Request  $request
     * @param   \Closure  $next
     * @return  mixed
     */
    public function handle($request, Closure $next)
    {
        $guard = session(config('laravel-impersonate.session_guard_using'), config('auth.defaults.guard'));

        if(!$this->auth->guard($guard)->check())
        {
          return Redirect::back();
        }

        return $next($request);
    }
}
