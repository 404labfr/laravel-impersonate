<?php

use Tests\Stubs\Models\User;

/**
 * @return  void
 */
beforeEach(function () {
    $this->app['view']->addLocation(__DIR__ . '/../Stubs/views/');
    $this->user = User::find(2);
    $this->admin = User::find(1);
})->group('blade');

it('displays can impersonate content directive', function () {
    $this->actingAs($this->admin);
    $view = makeView();
    $this->assertStringContainsString('Impersonate this user', $view);

    $this->admin->impersonate($this->user);
    $this->admin->leaveImpersonation();
    $view = makeView();
    $this->assertStringContainsString('Impersonate this user', $view);
    logout();
});

it('not displays can impersonate content directive', function () {
    $this->actingAs($this->user);
    $view = makeView();
    $this->assertStringNotContainsString('Impersonate this user', $view);
    logout();
});

it('displays impersonating content directive', function () {
    $this->actingAs($this->admin);
    $this->admin->impersonate($this->user);
    $view = makeView();
    $this->assertStringContainsString('Leave impersonation', $view);
    logout();
});

it('not displays impersonating content directive', function () {
    $this->actingAs($this->user);
    $view = makeView();
    $this->assertStringNotContainsString('Leave impersonation', $view);
    logout();

    $this->actingAs($this->admin);
    $this->admin->impersonate($this->user);
    $this->admin->leaveImpersonation();
    $view = makeView();
    $this->assertStringNotContainsString('Leave impersonation', $view);
    logout();
});

it('displays can be impersonated content directive', function () {
    $this->actingAs($this->admin);
    $view = makeView('can_be_impersonated', ['user' => $this->user]);
    $this->assertStringContainsString('Impersonate this user', $view);
    logout();

    $this->actingAs($this->admin);
    $this->admin->impersonate($this->user);
    $view = makeView('can_be_impersonated', ['user' => $this->user]);
    $this->assertStringNotContainsString('Impersonate this user', $view);
    logout();
});