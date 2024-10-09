<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use Lab404\Impersonate\Services\ImpersonateManager;
use Tests\Stubs\Models\User;
use Symfony\Component\HttpFoundation\ParameterBag;


/**
 * @return  void
 *
 * @throws BindingResolutionException
 */
beforeEach(function () {
    $this->manager = $this->app->make(ImpersonateManager::class);
    $this->firstGuard = 'web';
    $this->secondGuard = 'admin';
    $this->thirdGuard = 'otheruser';
});

it('can be accessed from container', function () {
    expect($this->manager)->toBeInstanceOf(ImpersonateManager::class);
    expect($this->app[ImpersonateManager::class])->toBeInstanceOf(ImpersonateManager::class);
    expect(app('impersonate'))->toBeInstanceOf(ImpersonateManager::class);
});

it('can find an user', function () {
    $admin = $this->manager->findUserById('admin@test.rocks', $this->firstGuard);
    $user = $this->manager->findUserById('user@test.rocks', $this->firstGuard);
    $superAdmin = $this->manager->findUserById('superadmin@test.rocks', $this->secondGuard);

    expect($admin)->toBeInstanceOf(User::class);
    expect($user)->toBeInstanceOf(User::class);
    expect($superAdmin)->toBeInstanceOf(User::class);
    expect($admin->name)->toEqual('Admin');
    expect($user->name)->toEqual('User');
    expect($superAdmin->name)->toEqual('SuperAdmin');
});

it('can verify impersonating', function () {
    expect($this->manager->isImpersonating())->toBeFalse();
    $this->app['session']->put($this->manager->getSessionKey(), 'admin@test.rocks');
    expect($this->manager->isImpersonating())->toBeTrue();
    expect($this->manager->getImpersonatorId())->toEqual('admin@test.rocks');
});

it('can clear impersonating', function () {
    $this->app['session']->put($this->manager->getSessionKey(), 'admin@test.rocks');
    $this->app['session']->put($this->manager->getSessionGuard(), 'guard_name');
    $this->app['session']->put($this->manager->getSessionGuardUsing(), 'guard_using_name');
    expect($this->app['session']->has($this->manager->getSessionKey()))->toBeTrue();
    expect($this->app['session']->has($this->manager->getSessionGuard()))->toBeTrue();
    expect($this->app['session']->has($this->manager->getSessionGuardUsing()))->toBeTrue();
    $this->manager->clear();
    expect($this->app['session']->has($this->manager->getSessionKey()))->toBeFalse();
    expect($this->app['session']->has($this->manager->getSessionGuard()))->toBeFalse();
    expect($this->app['session']->has($this->manager->getSessionGuardUsing()))->toBeFalse();
});

it('can take impersonating', function () {
    $this->app['auth']->guard($this->firstGuard)->loginUsingId('admin@test.rocks');
    expect($this->app['auth']->check())->toBeTrue();
    $this->manager->take($this->app['auth']->user(), $this->manager->findUserById('user@test.rocks', $this->firstGuard), $this->firstGuard);
    expect($this->app['auth']->user()->getAuthIdentifier())->toEqual('user@test.rocks');
    expect($this->manager->getImpersonatorId())->toEqual('admin@test.rocks');
    expect($this->manager->getImpersonatorGuardName())->toEqual($this->firstGuard);
    expect($this->manager->getImpersonatorGuardUsingName())->toEqual($this->firstGuard);
    expect($this->manager->isImpersonating())->toBeTrue();
});

it('can take impersonating other guard', function () {
    $this->app['auth']->guard($this->secondGuard)->loginUsingId('admin@test.rocks');
    expect($this->app['auth']->guard($this->secondGuard)->check())->toBeTrue();
    $this->manager->take(
        $this->app['auth']->guard($this->secondGuard)->user(),
        $this->manager->findUserById('superadmin@test.rocks', $this->firstGuard),
        $this->firstGuard
    );
    expect($this->app['auth']->user()->getAuthIdentifier())->toEqual('superadmin@test.rocks');
    expect($this->manager->getImpersonatorId())->toEqual('admin@test.rocks');
    expect($this->manager->getImpersonatorGuardName())->toEqual($this->secondGuard);
    expect($this->manager->getImpersonatorGuardUsingName())->toEqual($this->firstGuard);
    expect($this->manager->isImpersonating())->toBeTrue();
});

it('can leave impersonating', function () {
    $this->app['auth']->loginUsingId('admin@test.rocks');
    $this->manager->take($this->app['auth']->user(), $this->manager->findUserById('user@test.rocks', $this->firstGuard));
    expect($this->manager->leave())->toBeTrue();
    expect($this->manager->isImpersonating())->toBeFalse();
    expect($this->app['auth']->user())->toBeInstanceOf(User::class);
});

it('can leave impersonating other guard', function () {
    $this->app['auth']->guard($this->secondGuard)->loginUsingId('admin@test.rocks');
    $this->manager->take(
        $this->app['auth']->guard($this->secondGuard)->user(),
        $this->manager->findUserById('user@test.rocks', $this->firstGuard),
        $this->firstGuard
    );
    expect($this->manager->leave())->toBeTrue();
    expect($this->manager->isImpersonating())->toBeFalse();
    expect($this->app['auth']->guard($this->secondGuard)->user())->toBeInstanceOf(User::class);
});

it('keeps remember token when taking and leaving', function () {
    $admin = $this->manager->findUserById('admin@test.rocks', $this->firstGuard);
    $admin->remember_token = 'impersonator_token';
    $admin->save();

    $user = $this->manager->findUserById('user@test.rocks', $this->firstGuard);
    $user->remember_token = 'impersonated_token';
    $user->save();

    $admin->impersonate($user);
    $user->leaveImpersonation();

    $user->fresh();
    $admin->fresh();

    expect($admin->remember_token)->toEqual('impersonator_token');
    expect($user->remember_token)->toEqual('impersonated_token');
});

it('can get impersonator', function () {
    $this->app['auth']->loginUsingId('admin@test.rocks');
    expect($this->app['auth']->check())->toBeTrue();
    $this->manager->take($this->app['auth']->user(), $this->manager->findUserById('user@test.rocks'));
    expect($this->app['auth']->user()->getAuthIdentifier())->toEqual('user@test.rocks');
    expect($this->manager->getImpersonator()->id)->toEqual(1);
    expect($this->manager->getImpersonator()->name)->toEqual('Admin');
});

it('can get impersonator with guards from different tables', function () {
    $this->app['auth']->guard($this->thirdGuard)->loginUsingId('otheradmin@test.rocks');
    expect($this->app['auth']->guard($this->thirdGuard)->check())->toBeTrue();
    $this->manager->take(
        $this->app['auth']->guard($this->thirdGuard)->user(),
        $this->manager->findUserById('user@test.rocks', $this->firstGuard),
        $this->thirdGuard
    );

    # Impersonated user ("User" #2) is from table "users"
    expect($this->app['auth']->guard($this->thirdGuard)->user()->id)->toEqual(2);
    expect($this->app['auth']->guard($this->thirdGuard)->user()->name)->toEqual('User');
    expect($this->app['auth']->guard($this->thirdGuard)->user()->getTable())->toEqual('users');

    # Impersonating user ("OtherAdmin" #1) is from table "other_users"
    expect($this->manager->getImpersonator()->id)->toEqual(1);
    expect($this->manager->getImpersonator()->name)->toEqual('OtherAdmin');
    expect($this->manager->getImpersonator()->getTable())->toEqual('other_users');
});

function it_renames_the_remember_web_cookie_when_taking_and_reverts_the_change_when_leaving()
{
    app('router')->get('/cookie', function () {
        return 'hello';
    })->middleware([\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class]);

    $this->app['auth']->loginUsingId(1, true);
    $cookie = array_values($this->app['cookie']->getQueuedCookies())[0];
    $cookies = [$cookie->getName() => $cookie->getValue(), 'random' => 'cookie'];
    $this->app['request'] = (object) ['cookies' => new ParameterBag($cookies)];

    $this->manager->take($this->app['auth']->user(), $this->manager->findUserById('user@test.rocks'));
    expect(session()->all())->toHaveKey(ImpersonateManager::REMEMBER_PREFIX);
    expect(session()->get(ImpersonateManager::REMEMBER_PREFIX))->toEqual([$cookie->getName(), $cookie->getValue()]);

    // When user's session's auth !== the remember cookie's auth
    // Laravel seems to delete the cookie, so this is what we are faking
    $this->app['cookie']->unqueue($cookie->getName());

    $response = $this->get('/cookie');
    expect($response->headers->getCookies())->toHaveCount(0);

    $this->manager->leave();
    $this->assertArrayNotHasKey($cookie->getName(), session()->all());

    $response = $this->get('/cookie');
    $response->assertCookie($cookie->getName());
    expect($response->headers->getCookies()[0]->getName())->toEqual($cookie->getName());
    expect($response->headers->getCookies()[0]->getValue())->toEqual($cookie->getValue());
}