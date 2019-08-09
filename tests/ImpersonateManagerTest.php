<?php

namespace Lab404\Tests;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use Lab404\Impersonate\Services\ImpersonateManager;
use Lab404\Tests\Stubs\Models\User;
use Symfony\Component\HttpFoundation\ParameterBag;

class ImpersonateManagerTest extends TestCase
{
    /** @var  ImpersonateManager */
    protected $manager;

    public function setUp() : void
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

    /** @test */
    public function it_renames_the_remember_web_cookie_when_taking_and_reverts_the_change_when_leaving()
    {
        app('router')->get('/cookie', function () {
            return 'hello';
        })->middleware([\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class]);

        $this->app['auth']->loginUsingId(1, true);
        $cookie = array_values($this->app['cookie']->getQueuedCookies())[0];
        $cookies = [$cookie->getName() => $cookie->getValue(), 'random' => 'cookie'];
        $this->app['request'] = (object) ['cookies' => new ParameterBag($cookies)];

        $this->manager->take($this->app['auth']->user(), $this->manager->findUserById(2));
        $this->assertArrayHasKey(ImpersonateManager::REMEMBER_PREFIX, session()->all());
        $this->assertEquals([$cookie->getName(), $cookie->getValue()], session()->get(ImpersonateManager::REMEMBER_PREFIX));

        // When user's session's auth !== the remember cookie's auth
        // Laravel seems to delete the cookie, so this is what we are faking
        $this->app['cookie']->unqueue($cookie->getName());

        $response = $this->get('/cookie');
        $this->assertCount(0, $response->headers->getCookies());

        $this->manager->leave();
        $this->assertArrayNotHasKey($cookie->getName(), session()->all());

        $response = $this->get('/cookie');
        $response->assertCookie($cookie->getName());
        $this->assertEquals($cookie->getName(), $response->headers->getCookies()[0]->getName());
        $this->assertEquals($cookie->getValue(), $response->headers->getCookies()[0]->getValue());
    }
}
