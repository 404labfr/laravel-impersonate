<?php

use Illuminate\Http\Request;
use Lab404\Impersonate\Middleware\ProtectFromImpersonation;
use Tests\Stubs\Models\User;

/**
 * @return  void
 */
beforeEach(function () {
    $this->user = User::find(2);
    $this->admin = User::find(1);
    $this->request = new Request();
    $this->middleware = new ProtectFromImpersonation;
});

it('can acces when no impersonating', function () {
    $this->actingAs($this->user);
    $return = $this->middleware->handle($this->request, function () {
        return 'This is private';
    });

    expect($return)->toEqual('This is private');

    logout();
});

it('cant acces when impersonating', function () {
    $this->actingAs($this->admin);
    $this->admin->impersonate($this->user);

    $return = $this->middleware->handle($this->request, function () {
        return 'This is private';
    });

    $this->assertNotEquals('This is private', $return);
    logout();
});