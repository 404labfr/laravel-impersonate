<?php

namespace Lab404\Tests;

use Exception;
use Lab404\Impersonate\Services\ImpersonateManager;

class ModelImpersonateTest extends TestCase
{
    /**
     * Returns the user specified by id.
     *
     * @param  int $id
     * @return Illuminate\Database\Eloquent\Model
     */
    public function getUser($id)
    {
        return $this->app[ImpersonateManager::class]->findUserById($id);
    }

    /** @test */
    public function it_can_impersonate()
    {
        $user = $this->app['auth']->loginUsingId(1);
        $this->assertTrue($user->canImpersonate($this->getUser(2)));
    }

    /** @test */
    public function it_cant_impersonate()
    {
        $user = $this->app['auth']->loginUsingId(2);
        $this->assertFalse($user->canImpersonate($this->getUser(1)));
    }

    /** @test */
    public function it_can_be_impersonate()
    {
        $user = $this->app['auth']->loginUsingId(1);
        $this->assertTrue($user->canBeImpersonated($this->getUser(3)));
    }

    /** @test */
    public function it_cant_be_impersonate()
    {
        $user = $this->app['auth']->loginUsingId(3);
        $this->assertFalse($user->canBeImpersonated($this->getUser(1)));
    }

    /** @test */
    public function it_impersonates()
    {
        $admin = $this->app['auth']->loginUsingId(1);
        $this->assertFalse($admin->isImpersonated());
        $user  = $this->getUser(2);
        $admin->impersonate($user);
        $this->assertTrue($user->isImpersonated());
        $this->assertEquals($this->app['auth']->user()->getKey(), 2);
    }

    /** @test */
    public function it_can_leave_impersonation()
    {
        $admin = $this->app['auth']->loginUsingId(1);
        $user  = $this->getUser(2);
        $admin->impersonate($user);
        $admin->leaveImpersonation();
        $this->assertFalse($user->isImpersonated());
        $this->assertNotEquals($this->app['auth']->user()->getKey(), 2);
    }

    /** @test */
    public function a_user_model_can_decide_if_it_can_be_impersonated_based_on_user_impersonating()
    {
        $manager = $this->app['auth']->loginUsingId(4);
        $admin   = $this->getUser(1);

        try {
            $manager->impersonate($admin);
        } catch(Exception $e) {}

        $this->assertFalse($admin->isImpersonated());
        $this->assertNotEquals($this->app['auth']->user()->getKey(), 1);
    }
}
