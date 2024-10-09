<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use Lab404\Impersonate\Services\ImpersonateManager;

/**
 * @return void
 *
 * @throws BindingResolutionException
 */
beforeEach(function () {
    $this->manager = $this->app->make(ImpersonateManager::class);
    $this->guard = 'web';
});

it('can impersonate', function () {
    $user = $this->app['auth']->loginUsingId('admin@test.rocks');
    expect($user->canImpersonate())->toBeTrue();
});

it('cant impersonate', function () {
    $user = $this->app['auth']->loginUsingId('user@test.rocks');
    expect($user->canImpersonate())->toBeFalse();
});

it('can be impersonate', function () {
    $user = $this->app['auth']->loginUsingId('admin@test.rocks');
    expect($user->canBeImpersonated())->toBeTrue();
});

it('cant be impersonate', function () {
    $user = $this->app['auth']->loginUsingId('superadmin@test.rocks');
    expect($user->canBeImpersonated())->toBeFalse();
});

it('impersonates', function () {
    $admin = $this->app['auth']->loginUsingId('admin@test.rocks');
    expect($admin->isImpersonated())->toBeFalse();
    $user = $this->manager->findUserById('user@test.rocks', $this->guard);
    $admin->impersonate($user, $this->guard);
    expect($user->isImpersonated())->toBeTrue();
    expect('user@test.rocks')->toEqual($this->app['auth']->user()->getAuthIdentifier());
});

it('can leave impersonation', function () {
    $admin = $this->app['auth']->loginUsingId('admin@test.rocks');
    $user = $this->manager->findUserById('user@test.rocks', $this->guard);
    $admin->impersonate($user, $this->guard);
    $admin->leaveImpersonation();
    expect($user->isImpersonated())->toBeFalse();
    $this->assertNotEquals($this->app['auth']->user()->getAuthIdentifier(), 'user@test.rocks');
});