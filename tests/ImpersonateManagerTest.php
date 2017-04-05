<?php

namespace Lab404\Tests;

use Lab404\Impersonate\Services\ImpersonateManager;
use Lab404\Tests\Stubs\Models\User;

class ImpersonateManagerTest extends TestCase
{
    /** @var  ImpersonateManager */
    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = $this->app->make(ImpersonateManager::class);
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
        $admin = $this->manager->findUserById(1);
        $user = $this->manager->findUserById(2);

        $this->assertInstanceOf(User::class, $admin);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Admin', $admin->name);
        $this->assertEquals('User', $user->name);
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
        $this->assertTrue($this->app['session']->has($this->manager->getSessionKey()));
        $this->manager->clear();
        $this->assertFalse($this->app['session']->has($this->manager->getSessionKey()));
    }

    /** @test */
    public function it_can_take_impersonating()
    {
        $this->app['auth']->loginUsingId(1);
        $this->assertTrue($this->app['auth']->check());
        $this->manager->take($this->app['auth']->user(), $this->manager->findUserById(2));
        $this->assertEquals(2, $this->app['auth']->user()->getKey());
        $this->assertEquals(1, $this->manager->getImpersonatorId());
        $this->assertTrue($this->manager->isImpersonating());
    }

    /** @test */
    public function it_can_leave_impersonating()
    {
        $this->app['auth']->loginUsingId(1);
        $this->manager->take($this->app['auth']->user(), $this->manager->findUserById(2));
        $this->assertTrue($this->manager->leave());
        $this->assertFalse($this->manager->isImpersonating());
        $this->assertInstanceOf(User::class, $this->app['auth']->user());
    }

    /** @test */
    public function it_keeps_remember_token_when_taking_and_leaving()
    {
        $admin = $this->manager->findUserById(1);
        $admin->remember_token = 'impersonator_token';
        $admin->save();

        $user = $this->manager->findUserById(2);
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
