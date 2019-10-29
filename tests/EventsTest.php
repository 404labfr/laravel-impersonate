<?php

namespace Lab404\Tests;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;
use Lab404\Impersonate\Services\ImpersonateManager;
use Lab404\Tests\Stubs\Models\User;

class EventsTest extends TestCase
{
    /** @var  User $admin */
    protected $admin;
    /** @var  User $user */
    protected $user;
    /** @var  string $guard */
    protected $guard;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::find(1);
        $this->user = User::find(2);
        $this->guard = 'web';
    }

    /** @test */
    public function it_dispatches_events_when_taking_impersonation()
    {
        Event::fake();

        $admin = $this->admin;
        $user = $this->user;

        $this->assertTrue($admin->impersonate($user, $this->guard));

        Event::assertDispatched(TakeImpersonation::class, function ($event) use ($admin, $user) {
            return $event->impersonator->id == $admin->id && $event->impersonated->id == $user->id;
        });

        Event::assertNotDispatched(Login::class);
    }

    /** @test */
    public function it_dispatches_events_when_leaving_impersonation()
    {
        Event::fake();

        $admin = $this->admin;
        $user = $this->user;
        $this->app['auth']->loginUsingId($admin->id);

        $this->assertTrue($admin->impersonate($user, $this->guard));
        $this->assertTrue($user->leaveImpersonation());

        Event::assertDispatched(LeaveImpersonation::class, function ($event) use ($admin, $user) {
            return $event->impersonator->id == $admin->id && $event->impersonated->id == $user->id;
        });

        Event::assertNotDispatched(Logout::class);
    }

    /** @test */
    public function it_dispatches_login_event()
    {
        $manager = $this->app->make(ImpersonateManager::class);
        $manager->take($this->admin, $this->user, $this->guard);

        event(new Login($this->guard, $this->user, false));

        $this->assertFalse($this->app['session']->has($manager->getSessionKey()));
        $this->assertFalse($this->app['session']->has($manager->getSessionGuard()));
        $this->assertFalse($this->app['session']->has($manager->getSessionGuardUsing()));
    }

    /** @test */
    public function it_dispatches_logout_event()
    {
        $manager = $this->app->make(ImpersonateManager::class);
        $manager->take($this->admin, $this->user, $this->guard);

        event(new Logout($this->guard, $this->user));

        $this->assertFalse($this->app['session']->has($manager->getSessionKey()));
        $this->assertFalse($this->app['session']->has($manager->getSessionGuard()));
        $this->assertFalse($this->app['session']->has($manager->getSessionGuardUsing()));
    }
}
