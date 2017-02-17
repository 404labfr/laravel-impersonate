<?php

namespace Lab404\Impersonate\Middleware;

use Closure;
use Lab404\Impersonate\Services\ImpersonateManager;

class CantAccesIfImpersonate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $impersonate_manager = app()->make(ImpersonateManager::class);

        if ($impersonate_manager->isImpersonating()) {
            return redirect($impersonate_manager->getCantAccesIfImpersonateRedirectTo());
        }

        return $next($request);
    }
}
