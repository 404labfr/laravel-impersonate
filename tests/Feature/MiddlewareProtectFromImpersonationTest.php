<?php

use Illuminate\Http\Request;
use Lab404\Impersonate\Middleware\ProtectFromImpersonation;
use Tests\Stubs\Models\User;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::find(2);
    $this->admin = User::find(1);
    $this->request = new Request();
    $this->middleware = new ProtectFromImpersonation;
})->group('middleware');

it('can access when no impersonating', function () {
    actingAs($this->user);
    $return = $this->middleware->handle($this->request, function () {
        return 'This is private';
    });

    expect($return)->toEqual('This is private');

    logout();
});

it('cant access when impersonating', function () {
    actingAs($this->admin);
    $this->admin->impersonate($this->user);

    $return = $this->middleware->handle($this->request, function () {
        return 'This is private';
    });

    expect($return)->not->toEqual('This is private');
    logout();
});