<?php

namespace Lab404\Tests;

use Illuminate\Http\Request;
use Lab404\Tests\Stubs\Models\User;
use Lab404\Impersonate\Middleware\ProtectFromImpersonation;

class MiddlewareProtectFromImpersonationTest extends TestCase
{
    /** @var  User */
    protected $user;

    /** @var  User */
    protected $admin;

    /** @var  Request */
    protected $request;

    /** @var  ProtectFromImpersonation */
    protected $middleware;

    public function setUp()
    {
        parent::setUp();

        $this->user = User::find(2);
        $this->admin = User::find(1);
        $this->request = new Request();
        $this->middleware = new ProtectFromImpersonation;
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
    public function it_can_acces_when_no_impersonating()
    {
        $this->actingAs($this->user);
        $return = $this->middleware->handle($this->request, function () {
            return 'This is private';
        });

        $this->assertEquals('This is private', $return);

        $this->logout();
    }

    /** @test */
    public function it_cant_acces_when_impersonating()
    {
        $this->actingAs($this->admin);
        $this->admin->impersonate($this->user);
        $return = $this->middleware->handle($this->request, function () {
            return 'This is private';
        });

        $this->assertNotEquals('This is private', $return);

        $this->logout();
    }
}
