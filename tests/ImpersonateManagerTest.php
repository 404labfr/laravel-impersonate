<?php

namespace Lab404\Tests;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use Lab404\Impersonate\Services\ImpersonateManager;
use Lab404\Tests\Stubs\Models\User;
use Symfony\Component\HttpFoundation\ParameterBag;

class ImpersonateManagerTest extends TestCase
{
    /** @var  ImpersonateManager */
    protected $manager;
    /** @var  string */
    protected $firstGuard;
    /** @var  string */
    protected $secondGuard;

    public function setUp() : void
    {
        parent::setUp();

        $this->manager = $this->app->make(ImpersonateManager::class);
        $this->firstGuard = 'web';
        $this->secondGuard = 'admin';
    }

    /** @test */
    public function it_can_be_accessed_from_container()
    {
        $this->assertInstanceOf(ImpersonateManager::class, $this->manager);
        $this->assertInstanceOf(ImpersonateManager::class, $this->app[ImpersonateManager::class]);
        $this->assertInstanceOf(ImpersonateManager::class, app('impersonate'));
    }

    /** @test */
    public function it_can_find_an_user()
    {
        $admin = $this->manager->findUserById('admin@test.rocks', $this->firstGuard);
        $user = $this->manager->findUserById('user@test.rocks', $this->firstGuard);
        $superAdmin = $this->manager->findUserById('superadmin@test.rocks', $this->secondGuard);

        $this->assertInstanceOf(User::class, $admin);
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(User::class, $superAdmin);
        $this->assertEquals('Admin', $admin->name);
        $this->assertEquals('User', $user->name);
        $this->assertEquals('SuperAdmin', $superAdmin->name);
    }

    /** @test */
    public function it_can_verify_impersonating()
    {
        $this->assertFalse($this->manager->isImpersonating());
        $this->app['session']->put($this->manager->getSessionKey(), 'admin@test.rocks');
        $this->assertTrue($this->manager->isImpersonating());
        $this->assertEquals('admin@test.rocks', $this->manager->getImpersonatorId());
    }

    /** @test */
    public function it_can_clear_impersonating()
    {
        $this->app['session']->put($this->manager->getSessionKey(), 'admin@test.rocks');
        $this->app['session']->put($this->manager->getSessionGuard(), 'guard_name');
        $this->app['session']->put($this->manager->getSessionGuardUsing(), 'guard_using_name');
        $this->assertTrue($this->app['session']->has($this->manager->getSessionKey()));
        $this->assertTrue($this->app['session']->has($this->manager->getSessionGuard()));
        $this->assertTrue($this->app['session']->has($this->manager->getSessionGuardUsing()));
        $this->manager->clear();
        $this->assertFalse($this->app['session']->has($this->manager->getSessionKey()));
        $this->assertFalse($this->app['session']->has($this->manager->getSessionGuard()));
        $this->assertFalse($this->app['session']->has($this->manager->getSessionGuardUsing()));
    }

    /** @test */
    public function it_can_take_impersonating()
    {
        $this->app['auth']->guard($this->firstGuard)->loginUsingId('admin@test.rocks');
        $this->assertTrue($this->app['auth']->check());
        $this->manager->take($this->app['auth']->user(), $this->manager->findUserById('user@test.rocks', $this->firstGuard), $this->firstGuard);
        $this->assertEquals('user@test.rocks', $this->app['auth']->user()->getAuthIdentifier());
        $this->assertEquals('admin@test.rocks', $this->manager->getImpersonatorId());
        $this->assertEquals($this->firstGuard, $this->manager->getImpersonatorGuardName());
        $this->assertEquals($this->firstGuard, $this->manager->getImpersonatorGuardUsingName());
        $this->assertTrue($this->manager->isImpersonating());
    }

    /** @test */
    public function it_can_take_impersonating_other_guard()
    {
        $this->app['auth']->guard($this->secondGuard)->loginUsingId('admin@test.rocks');
        $this->assertTrue($this->app['auth']->guard($this->secondGuard)->check());
        $this->manager->take(
            $this->app['auth']->guard($this->secondGuard)->user(),
            $this->manager->findUserById('superadmin@test.rocks', $this->firstGuard),
            $this->firstGuard
        );
        $this->assertEquals('superadmin@test.rocks', $this->app['auth']->user()->getAuthIdentifier());
        $this->assertEquals('admin@test.rocks', $this->manager->getImpersonatorId());
        $this->assertEquals($this->secondGuard, $this->manager->getImpersonatorGuardName());
        $this->assertEquals($this->firstGuard, $this->manager->getImpersonatorGuardUsingName());
        $this->assertTrue($this->manager->isImpersonating());
    }

    /** @test */
    public function it_can_leave_impersonating()
    {
        $this->app['auth']->loginUsingId('admin@test.rocks');
        $this->manager->take($this->app['auth']->user(), $this->manager->findUserById('user@test.rocks', $this->firstGuard));
        $this->assertTrue($this->manager->leave());
        $this->assertFalse($this->manager->isImpersonating());
        $this->assertInstanceOf(User::class, $this->app['auth']->user());
    }

    /** @test */
    public function it_can_leave_impersonating_other_guard()
    {
        $this->app['auth']->guard($this->secondGuard)->loginUsingId('admin@test.rocks');
        $this->manager->take(
            $this->app['auth']->guard($this->secondGuard)->user(),
            $this->manager->findUserById('user@test.rocks', $this->firstGuard),
            $this->firstGuard
        );
        $this->assertTrue($this->manager->leave());
        $this->assertFalse($this->manager->isImpersonating());
        $this->assertInstanceOf(User::class, $this->app['auth']->guard($this->secondGuard)->user());
    }

    /** @test */
    public function it_keeps_remember_token_when_taking_and_leaving()
    {
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

        $this->assertEquals('impersonator_token', $admin->remember_token);
        $this->assertEquals('impersonated_token', $user->remember_token);
    }

    /** @test */
    public function it_can_get_impersonator()
    {
        $this->app['auth']->loginUsingId('admin@test.rocks');
        $this->assertTrue($this->app['auth']->check());
        $this->manager->take($this->app['auth']->user(), $this->manager->findUserById('user@test.rocks'));
        $this->assertEquals('user@test.rocks', $this->app['auth']->user()->getAuthIdentifier());
        $this->assertEquals(1, $this->manager->getImpersonator()->id);
        $this->assertEquals('Admin', $this->manager->getImpersonator()->name);
    }

    public function it_renames_the_remember_web_cookie_when_taking_and_reverts_the_change_when_leaving()
    {
        app('router')->get('/cookie', function () {
            return 'hello';
        })->middleware([\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class]);

        $this->app['auth']->loginUsingId(1, true);
        $cookie = array_values($this->app['cookie']->getQueuedCookies())[0];
        $cookies = [$cookie->getName() => $cookie->getValue(), 'random' => 'cookie'];
        $this->app['request'] = (object) ['cookies' => new ParameterBag($cookies)];

        $this->manager->take($this->app['auth']->user(), $this->manager->findUserById('user@test.rocks'));
        $this->assertArrayHasKey(ImpersonateManager::REMEMBER_PREFIX, session()->all());
        $this->assertEquals([$cookie->getName(), $cookie->getValue()], session()->get(ImpersonateManager::REMEMBER_PREFIX));

        // When user's session's auth !== the remember cookie's auth
        // Laravel seems to delete the cookie, so this is what we are faking
        $this->app['cookie']->unqueue($cookie->getName());

        $response = $this->get('/cookie');
        $this->assertCount(0, $response->headers->getCookies());

        $this->manager->leave();
        $this->assertArrayNotHasKey($cookie->getName(), session()->all());

        $response = $this->get('/cookie');
        $response->assertCookie($cookie->getName());
        $this->assertEquals($cookie->getName(), $response->headers->getCookies()[0]->getName());
        $this->assertEquals($cookie->getValue(), $response->headers->getCookies()[0]->getValue());
    }
}
