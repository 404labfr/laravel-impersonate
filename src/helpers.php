<?php

use Illuminate\Contracts\Auth\Authenticatable;

if (! function_exists('can_impersonate')) {

	/**
	 * Check whether the current user is authorized to impersonate.
	 *
	 * @param  null  $guard
	 * @return bool
	 */
	function can_impersonate(?string $guard = null): bool
	{
		$guard = $guard ?? app('impersonate')->getCurrentAuthGuardName();

		return app('auth')->guard($guard)->check()
            && app('auth')->guard($guard)->user()->canImpersonate();
	}
}

if (! function_exists('can_be_impersonated')) {

	/**
	 * Check whether the specified user can be impersonated.
	 *
	 * @param  Authenticatable  $user
	 * @param  string|null      $guard
	 * @return bool
	 */
		function can_be_impersonated(Authenticatable $user, ?string $guard = null): bool
	{
		$guard = $guard ?? app('impersonate')->getCurrentAuthGuardName();
		return app('auth')->guard($guard)->check()
		       && app('auth')->guard($guard)->user()->isNot($user)
		       && $user->canBeImpersonated();
	}
}

if (! function_exists('is_impersonating')) {

	/**
	 * Check whether the current user is being impersonated.
	 *
	 * @param  string|null  $guard
	 * @return bool
	 */
	function is_impersonating(?string $guard = null): bool
	{
		$guard = $guard ?? app('impersonate')->getCurrentAuthGuardName();

		return app('auth')->guard($guard)->check()
            && app('auth')->guard($guard)->user()->isImpersonated();
	}
}
