<?php

namespace Lab404\Tests\Feature;

use Illuminate\Contracts\Container\BindingResolutionException;
use Lab404\Impersonate\Services\ImpersonateManager;
use Lab404\Tests\TestCase;

class ModelImpersonateTest extends TestCase
{
    protected ImpersonateManager $manager;

    protected string $guard;

    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->app->make(ImpersonateManager::class);
        $this->guard = 'web';
    }

    /** @test */
    public function it_can_impersonate()
    {
        $user = $this->app['auth']->loginUsingId('admin@test.rocks');
        $this->assertTrue($user->canImpersonate());
    }

    /** @test */
    public function it_cant_impersonate()
    {
        $user = $this->app['auth']->loginUsingId('user@test.rocks');
        $this->assertFalse($user->canImpersonate());
    }

    /** @test */
    public function it_can_be_impersonate()
    {
        $user = $this->app['auth']->loginUsingId('admin@test.rocks');
        $this->assertTrue($user->canBeImpersonated());
    }

    /** @test */
    public function it_cant_be_impersonate()
    {
        $user = $this->app['auth']->loginUsingId('superadmin@test.rocks');
        $this->assertFalse($user->canBeImpersonated());
    }

    /** @test */
    public function it_impersonates()
    {
        $admin = $this->app['auth']->loginUsingId('admin@test.rocks');
        $this->assertFalse($admin->isImpersonated());
        $user = $this->manager->findUserById('user@test.rocks', $this->guard);
        $admin->impersonate($user, $this->guard);
        $this->assertTrue($user->isImpersonated());
        $this->assertEquals($this->app['auth']->user()->getAuthIdentifier(), 'user@test.rocks');
    }

    /** @test */
    public function it_can_leave_impersonation()
    {
        $admin = $this->app['auth']->loginUsingId('admin@test.rocks');
        $user = $this->manager->findUserById('user@test.rocks', $this->guard);
        $admin->impersonate($user, $this->guard);
        $admin->leaveImpersonation();
        $this->assertFalse($user->isImpersonated());
        $this->assertNotEquals($this->app['auth']->user()->getAuthIdentifier(), 'user@test.rocks');
    }
}
