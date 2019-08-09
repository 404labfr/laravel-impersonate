<?php

namespace Lab404\Tests;

use Lab404\Impersonate\Services\ImpersonateManager;

class ModelImpersonateTest extends TestCase
{
    /** @var  ImpersonateManager $manager */
    protected $manager;

    /** @var  string $guard */
    protected $guard;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->app->make(ImpersonateManager::class);

        $this->guard = 'web';
    }

    /** @test */
    public function it_can_impersonate()
    {
        $user = $this->app['auth']->loginUsingId(1);
        $this->assertTrue($user->canImpersonate());
    }

    /** @test */
    public function it_cant_impersonate()
    {
        $user = $this->app['auth']->loginUsingId(2);
        $this->assertFalse($user->canImpersonate());
    }

    /** @test */
    public function it_can_be_impersonate()
    {
        $user = $this->app['auth']->loginUsingId(1);
        $this->assertTrue($user->canBeImpersonated());
    }

    /** @test */
    public function it_cant_be_impersonate()
    {
        $user = $this->app['auth']->loginUsingId(3);
        $this->assertFalse($user->canBeImpersonated());
    }

    /** @test */
    public function it_impersonates()
    {
        $admin = $this->app['auth']->loginUsingId(1);
        $this->assertFalse($admin->isImpersonated());
        $user = $this->manager->findUserById(2, $this->guard);
        $admin->impersonate($user, $this->guard);
        $this->assertTrue($user->isImpersonated());
        $this->assertEquals($this->app['auth']->user()->getKey(), 2);
    }

    /** @test */
    public function it_can_leave_impersonation()
    {
        $admin = $this->app['auth']->loginUsingId(1);
        $user = $this->manager->findUserById(2, $this->guard);
        $admin->impersonate($user, $this->guard);
        $admin->leaveImpersonation();
        $this->assertFalse($user->isImpersonated());
        $this->assertNotEquals($this->app['auth']->user()->getKey(), 2);
    }
}
