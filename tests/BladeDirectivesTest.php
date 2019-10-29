<?php

namespace Lab404\Tests;

use Lab404\Tests\Stubs\Models\User;

class BladeDirectivesTest extends TestCase
{
    /** @var  User $user */
    protected $user;
    /** @var  User $admin */
    protected $admin;
    /** @var  string $view */
    protected $view;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['view']->addLocation(__DIR__ . '/Stubs/views/');
        $this->user = User::find(2);
        $this->admin = User::find(1);
    }

    /**
     * @param string $view
     * @param array  $with
     * @return  void
     */
    protected function makeView($view = 'impersonate', array $with = [])
    {
        $this->view = $this->app['view']->make($view, $with)->render();
    }

    /**
     * @param void
     * @return  void
     */
    protected function logout()
    {
        $this->app['auth']->logout();
    }

    /** @test */
    public function it_displays_can_impersonate_content_directive()
    {
        $this->actingAs($this->admin);
        $this->makeView();
        $this->assertStringContainsString('Impersonate this user', $this->view);

        $this->admin->impersonate($this->user);
        $this->admin->leaveImpersonation();
        $this->makeView();
        $this->assertStringContainsString('Impersonate this user', $this->view);
        $this->logout();
    }

    /** @test */
    public function it_not_displays_can_impersonate_content_directive()
    {
        $this->actingAs($this->user);
        $this->makeView();
        $this->assertStringNotContainsString('Impersonate this user', $this->view);
        $this->logout();
    }

    /** @test */
    public function it_displays_impersonating_content_directive()
    {
        $this->actingAs($this->admin);
        $this->admin->impersonate($this->user);
        $this->makeView();
        $this->assertStringContainsString('Leave impersonation', $this->view);
        $this->logout();
    }

    /** @test */
    public function it_not_displays_impersonating_content_directive()
    {
        $this->actingAs($this->user);
        $this->makeView();
        $this->assertStringNotContainsString('Leave impersonation', $this->view);
        $this->logout();

        $this->actingAs($this->admin);
        $this->admin->impersonate($this->user);
        $this->admin->leaveImpersonation();
        $this->makeView();
        $this->assertStringNotContainsString('Leave impersonation', $this->view);
        $this->logout();
    }

    /** @test */
    public function it_displays_can_be_impersonated_content_directive()
    {
        $this->actingAs($this->admin);
        $this->makeView('can_be_impersonated', ['user' => $this->user]);
        $this->assertStringContainsString('Impersonate this user', $this->view);
        $this->logout();

        $this->actingAs($this->admin);
        $this->admin->impersonate($this->user);
        $this->makeView('can_be_impersonated', ['user' => $this->user]);
        $this->assertStringNotContainsString('Impersonate this user', $this->view);
        $this->logout();
    }
}
