<?php

namespace Lab404\Tests;

use Illuminate\Support\Facades\Hash;
use Lab404\Tests\Stubs\Models\User;

class SessionGuardTest extends TestCase
{
    /** @var String $guard */
    private $guard;

    public function setUp(): void
    {
        parent::setUp();
        $this->guard = 'web';
    }

    /** @test */
    public function it_updates_password_hash()
    {
        $hashName = 'password_hash_' . $this->guard;
        $this->app['auth']->guard($this->guard)->loginUsingId('admin@test.rocks');
        $startHash = Hash::make(auth()->user()->password);
        $this->app['auth']->guard($this->guard)->getSession()->put($hashName, $startHash);
        $this->app['auth']->guard($this->guard)->quietLogout();
        $this->app['auth']->guard($this->guard)->quietLogin(
            User::where('email', 'different-password-user@test.rocks')->first()
        );
        $this->assertNotEquals($startHash, $this->app['auth']->guard($this->guard)->getSession()->get($hashName));
    }
}
