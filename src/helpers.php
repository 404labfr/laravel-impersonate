<?php

if (! function_exists('can_impersonate')) {

	/**
	 * Check whether the current user is authorized to impersonate.
	 *
	 * @param  null  $guard
	 * @return bool
	 */
	function can_impersonate($guard = null): bool
	{
		$guard = $guard ?? app('impersonate')->getCurrentAuthGuardName();

		return app()['auth']->guard($guard)->check() && app()['auth']->guard($guard)->canImpersonate();
	}
}
