<?php

namespace Lab404\Tests;

use Lab404\Impersonate\Services\ImpersonateManager;
use Lab404\Tests\Stubs\Models\User;

class ImpersonateManagerTest extends TestCase
{
    /** @var  ImpersonateManager */
    protected $manager;

    /** @var  string */
    protected $firstGuard;

    /** @var  string */
    protected $secondGuard;

    public function setUp()
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
        $admin = $this->manager->findUserById(1, $this->firstGuard);
        $user = $this->manager->findUserById(2, $this->firstGuard);
        $superAdmin = $this->manager->findUserById(3, $this->secondGuard);

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
        $this->app['session']->put($this->manager->getSessionKey(), 1);
        $this->assertTrue($this->manager->isImpersonating());
        $this->assertEquals(1, $this->manager->getImpersonatorId());
    }

    /** @test */
    public function it_can_clear_impersonating()
    {
        $this->app['session']->put($this->manager->getSessionKey(), 1);
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
        $this->app['auth']->guard($this->firstGuard)->loginUsingId(1);
        $this->assertTrue($this->app['auth']->check());
        $this->manager->take($this->app['auth']->user(), $this->manager->findUserById(2, $this->firstGuard), $this->firstGuard);
        $this->assertEquals(2, $this->app['auth']->user()->getKey());
        $this->assertEquals(1, $this->manager->getImpersonatorId());
        $this->assertEquals($this->firstGuard, $this->manager->getImpersonatorGuardName());
        $this->assertEquals($this->firstGuard, $this->manager->getImpersonatorGuardUsingName());
        $this->assertTrue($this->manager->isImpersonating());
    }

    /** @test */
    public function it_can_take_impersonating_other_guard()
    {
        $this->app['auth']->guard($this->secondGuard)->loginUsingId(1);
        $this->assertTrue($this->app['auth']->guard($this->secondGuard)->check());
        $this->manager->take(
            $this->app['auth']->guard($this->secondGuard)->user(),
            $this->manager->findUserById(3, $this->firstGuard),
            $this->firstGuard
        );
        $this->assertEquals(3, $this->app['auth']->user()->getKey());
        $this->assertEquals(1, $this->manager->getImpersonatorId());
        $this->assertEquals($this->secondGuard, $this->manager->getImpersonatorGuardName());
        $this->assertEquals($this->firstGuard, $this->manager->getImpersonatorGuardUsingName());
        $this->assertTrue($this->manager->isImpersonating());
    }

    /** @test */
    public function it_can_leave_impersonating()
    {
        $this->app['auth']->loginUsingId(1);
        $this->manager->take($this->app['auth']->user(), $this->manager->findUserById(2, $this->firstGuard));
        $this->assertTrue($this->manager->leave());
        $this->assertFalse($this->manager->isImpersonating());
        $this->assertInstanceOf(User::class, $this->app['auth']->user());
    }

    /** @test */
    public function it_can_leave_impersonating_other_guard()
    {
        $this->app['auth']->guard($this->secondGuard)->loginUsingId(1);
        $this->manager->take(
            $this->app['auth']->guard($this->secondGuard)->user(),
            $this->manager->findUserById(2, $this->firstGuard),
            $this->firstGuard
        );
        $this->assertTrue($this->manager->leave());
        $this->assertFalse($this->manager->isImpersonating());
        $this->assertInstanceOf(User::class, $this->app['auth']->guard($this->secondGuard)->user());
    }

    /** @test */
    public function it_keeps_remember_token_when_taking_and_leaving()
    {
        $admin = $this->manager->findUserById(1, $this->firstGuard);
        $admin->remember_token = 'impersonator_token';
        $admin->save();

        $user = $this->manager->findUserById(2, $this->firstGuard);
        $user->remember_token = 'impersonated_token';
        $user->save();

        $admin->impersonate($user);
        $user->leaveImpersonation();

        $user->fresh();
        $admin->fresh();

        $this->assertEquals('impersonator_token', $admin->remember_token);
        $this->assertEquals('impersonated_token', $user->remember_token);
    }
}
