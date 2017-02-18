<?php

namespace Lab404\Tests;

use Illuminate\View\Factory;
use Lab404\Tests\Stubs\Models\User;

class BladeDirectivesTest extends TestCase
{
    /** @var  User */
    protected $user;

    /** @var  User */
    protected $admin;

    /** @var  string */
    protected $view;

    public function setUp()
    {
        parent::setUp();

        $this->app['view']->addLocation(__DIR__ . '/Stubs/views/');
        $this->user = User::find(2);
        $this->admin = User::find(1);
    }

    /**
     * @param   void
     * @return  void
     */
    protected function makeView()
    {
        $this->view = (string) $this->app['view']->make('impersonate');
    }

    /**
     * @param   void
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
        $this->assertContains('Impersonate this user', $this->view);

        $this->admin->impersonate($this->user);
        $this->admin->leaveImpersonation();
        $this->makeView();
        $this->assertContains('Impersonate this user', $this->view);
        $this->logout();
    }

    /** @test */
    public function it_not_displays_can_impersonate_content_directive()
    {
        $this->actingAs($this->user);
        $this->makeView();
        $this->assertNotContains('Impersonate this user', $this->view);
        $this->logout();
    }

    /** @test */
    public function it_displays_impersonating_content_directive()
    {
        $this->actingAs($this->admin);
        $this->admin->impersonate($this->user);
        $this->makeView();
        $this->assertContains('Leave impersonation', $this->view);
        $this->logout();
    }

    /** @test */
    public function it_not_displays_impersonating_content_directive()
    {
        $this->actingAs($this->user);
        $this->makeView();
        $this->assertNotContains('Leave impersonation', $this->view);
        $this->logout();

        $this->actingAs($this->admin);
        $this->admin->impersonate($this->user);
        $this->admin->leaveImpersonation();
        $this->makeView();
        $this->assertNotContains('Leave impersonation', $this->view);
        $this->logout();
    }
}
