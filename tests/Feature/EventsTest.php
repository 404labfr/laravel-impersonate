<?php

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;
use Lab404\Impersonate\Services\ImpersonateManager;
use Tests\Stubs\Models\User;

/**
 * @return  void
 */
beforeEach(function () {
    $this->admin = User::find(1);
    $this->user = User::find(2);
    $this->guard = 'web';
});

it('dispatches events when taking impersonation', function () {
    Event::fake();

    $admin = $this->admin;
    $user = $this->user;

    expect($admin->impersonate($user, $this->guard))->toBeTrue();

    Event::assertDispatched(TakeImpersonation::class, function ($event) use ($admin, $user) {
        return $event->impersonator->id == $admin->id && $event->impersonated->id == $user->id;
    });

    Event::assertNotDispatched(Login::class);
});

it('dispatches events when leaving impersonation', function () {
    Event::fake();

    $admin = $this->admin;
    $user = $this->user;
    $this->app['auth']->loginUsingId($admin->id);

    expect($admin->impersonate($user, $this->guard))->toBeTrue();
    expect($user->leaveImpersonation())->toBeTrue();

    Event::assertDispatched(LeaveImpersonation::class, function ($event) use ($admin, $user) {
        return $event->impersonator->id == $admin->id && $event->impersonated->id == $user->id;
    });

    Event::assertNotDispatched(Logout::class);
});

it('dispatches login event', function () {
    $manager = $this->app->make(ImpersonateManager::class);
    $manager->take($this->admin, $this->user, $this->guard);

    event(new Login($this->guard, $this->user, false));

    expect($this->app['session']->has($manager->getSessionKey()))->toBeFalse();
    expect($this->app['session']->has($manager->getSessionGuard()))->toBeFalse();
    expect($this->app['session']->has($manager->getSessionGuardUsing()))->toBeFalse();
});

it('dispatches logout event', function () {
    $manager = $this->app->make(ImpersonateManager::class);
    $manager->take($this->admin, $this->user, $this->guard);

    event(new Logout($this->guard, $this->user));

    expect($this->app['session']->has($manager->getSessionKey()))->toBeFalse();
    expect($this->app['session']->has($manager->getSessionGuard()))->toBeFalse();
    expect($this->app['session']->has($manager->getSessionGuardUsing()))->toBeFalse();
});