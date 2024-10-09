<?php

namespace Tests\Feature;

use Tests\Stubs\Models\User;
use Tests\TestCase;

class BladeDirectivesTest extends TestCase
{
    protected User $user;
    protected User $admin;
    protected string $view;

    /**
     * @return  void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->app['view']->addLocation(__DIR__ . '/../Stubs/views/');
        $this->user = User::find(2);
        $this->admin = User::find(1);
    }

    /**
     * @param  string  $view
     * @param array  $with
     *
     * @return  void
     */
    protected function makeView(string $view = 'impersonate', array $with = []): void
    {
        $this->view = $this->app['view']->make($view, $with)->render();
    }

    /**
     * @return  void
     */
    protected function logout(): void
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
