<?php

namespace Lab404\Tests;

use Lab404\Impersonate\Services\ImpersonateManager;

class ControllerTest extends TestCase
{
    /** @var  ImpersonateManager $manager */
    protected $manager;

    protected $defaultGuard = "web";

    protected $otherGuard = "otheruser";

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->app->make(ImpersonateManager::class);
    }

    /** @test */
    function it_can_impersonate_using_route()
    {
        $admin = $this->app['auth']->loginUsingId('admin@test.rocks');
        $this->assertFalse($admin->isImpersonated());

        $response = $this->get(route('impersonate', ['user@test.rocks', $this->defaultGuard]));

        $response->assertRedirect('/');
        $this->assertTrue($this->manager->isImpersonating());
    }

    /** @test */
    function it_can_leave_using_route()
    {
        $admin = $this->app['auth']->loginUsingId('admin@test.rocks');
        $this->assertFalse($admin->isImpersonated());

        $this->get(route('impersonate', ['user@test.rocks', $this->defaultGuard]));
        $this->assertTrue($this->manager->isImpersonating());

        $response = $this->get(route('impersonate.leave'));
        $response->assertRedirect('/');
        $this->assertFalse($this->manager->isImpersonating());
    }

    /** @test */
    function it_can_leave_with_different_redirection()
    {
        $admin = $this->app['auth']->guard($this->otherGuard)->loginUsingId('otheradmin@test.rocks');
        $this->assertFalse($admin->isImpersonated());

        $otherUser = $this->manager->findUserById('otheruser@test.rocks', $this->otherGuard);
        $admin->impersonate($otherUser, $this->otherGuard);
        $this->assertTrue($this->manager->isImpersonating());

        $response = $this->get(route('impersonate.leave'));
        $response->assertRedirect('/home');
        $this->assertFalse($this->manager->isImpersonating());
    }
}
