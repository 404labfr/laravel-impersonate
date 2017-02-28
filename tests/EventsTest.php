<?php

namespace Lab404\Tests;

use Illuminate\Events\Dispatcher;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;
use Lab404\Impersonate\Services\ImpersonateManager;
use Lab404\Tests\Stubs\Models\User;

class EventsTest extends TestCase
{
    /** @var  ImpersonateManager */
    protected $manager;

    /** @var  Dispatcher */
    protected $dispatcher;

    /** @var  User */
    protected $admin;

    /** @var  User */
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->manager = $this->app->make(ImpersonateManager::class);
        $this->dispatcher = $this->app['events'];
        $this->admin = User::find(1);
        $this->user = User::find(2);

        $this->dispatcher->listen(TakeImpersonation::class, function ($impersonator, $impersonated)
        {
            $this->assertEquals(1, $impersonator->getKey());
            $this->assertEquals(2, $impersonated->getKey());
        });

        $this->dispatcher->listen(LeaveImpersonation::class, function ($impersonator, $impersonated)
        {
            $this->assertEquals(1, $impersonator->getKey());
            $this->assertEquals(2, $impersonated->getKey());
        });
    }

    /** @test */
    public function it_dispatch_make_impersonation_event()
    {
        $this->dispatcher->fire(TakeImpersonation::class, [$this->admin, $this->user]);
    }

    /** @test */
    public function it_dispatch_leave_impersonation_event()
    {
        $this->dispatcher->fire(LeaveImpersonation::class, [$this->admin, $this->user]);
    }
}
