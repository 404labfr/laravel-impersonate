<?php

use Tests\Stubs\Models\User;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->app['view']->addLocation(__DIR__ . '/../Stubs/views/');
    $this->user = User::find(2);
    $this->admin = User::find(1);
})->group('blade');

it('displays can impersonate content directive', function () {
    actingAs($this->admin);

    expect(makeView())->toContain('Impersonate this user');

    $this->admin->impersonate($this->user);
    $this->admin->leaveImpersonation();

    expect(makeView())->toContain('Impersonate this user');

    logout();
});

it('not displays can impersonate content directive', function () {
    actingAs($this->user);

    expect(makeView())->not->toContain('Impersonate this user');

    logout();
});

it('displays impersonating content directive', function () {
    actingAs($this->admin);

    $this->admin->impersonate($this->user);

    expect(makeView())->toContain('Leave impersonation');

    logout();
});

it('not displays impersonating content directive', function () {
    actingAs($this->user);

    expect(makeView())->not->toContain('Leave impersonation');

    logout();
    actingAs($this->admin);

    $this->admin->impersonate($this->user);
    $this->admin->leaveImpersonation();

    expect(makeView())->not->toContain('Leave impersonation');

    logout();
});

it('displays can be impersonated content directive', function () {
    actingAs($this->admin);
    expect(makeView('can_be_impersonated', ['user' => $this->user]))
        ->toContain('Impersonate this user');
    logout();

    actingAs($this->admin);
    $this->admin->impersonate($this->user);
    expect(makeView('can_be_impersonated', ['user' => $this->user]))
        ->not->toContain('Impersonate this user');
    logout();
});