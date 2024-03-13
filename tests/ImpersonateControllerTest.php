<?php

namespace Lab404\Tests;

use Lab404\Impersonate\Services\ImpersonateManager;

class ImpersonateControllerTest extends TestCase
{
    /** @var  ImpersonateManager $manager */
    protected $manager;

    /** @var  string $guard */
    protected $guard;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->app->make(ImpersonateManager::class);
        $this->guard = 'web';
    }

    /** @test */
    public function it_gets_leave_redirect_to_from_session_if_exists()
    {
        // Arrange
        $leaveRedirectTo = 'http://example.com';
        $this->app['session']->put($this->manager->getSessionLeaveRedirectTo(), $leaveRedirectTo);
        $this->app['session']->put($this->manager->getSessionKey(), 'test_session_key');

        // Act
        $response = $this->get(route('impersonate.leave'));

        // Assert
        $response->assertRedirect($leaveRedirectTo);
    }

    /** @test */
    public function it_gets_leave_redirect_to_from_query_parameter_and_stores_it_in_session()
    {
        // Arrange
        $this->withoutExceptionHandling();
        $this->app['auth']->loginUsingId('admin@test.rocks');
        $leaveRedirectTo = 'http://example.com';

        // Act
        $response = $this->get(route('impersonate', ['id' => 'user@test.rocks', 'guardName' => 'web', 'leaveRedirectTo' => $leaveRedirectTo]));

        // Assert
        $this->assertEquals($leaveRedirectTo, $this->app['session']->get($this->manager->getSessionLeaveRedirectTo()));
    }
}
