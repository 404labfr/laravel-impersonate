<?php

namespace Lab404\Impersonate\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;
use Lab404\Impersonate\Services\ImpersonateManager;

class ProtectFromImpersonation
{
    /**
     * Handle an incoming request.
     *
     * @param   \Illuminate\Http\Request  $request
     * @param   \Closure  $next
     * @return  mixed
     */
    public function handle($request, Closure $next)
    {
        $impersonate_manager = app()->make(ImpersonateManager::class);

        if ($impersonate_manager->isImpersonating()) {
            return Redirect::back();
        }

        return $next($request);
    }
}
