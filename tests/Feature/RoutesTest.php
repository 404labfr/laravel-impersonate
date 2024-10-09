<?php

beforeEach(function () {
    $this->routes = $this->app['router']->getRoutes();
});

it('adds impersonate route', function () {
    expect((bool) $this->routes->getByName('impersonate'))->toBeTrue();
    expect((bool) $this->routes->getByAction('Lab404\Impersonate\Controllers\ImpersonateController@take'))->toBeTrue();
});

it('adds leave route', function () {
    expect((bool) $this->routes->getByName('impersonate.leave'))->toBeTrue();
    expect((bool) $this->routes->getByAction('Lab404\Impersonate\Controllers\ImpersonateController@leave'))->toBeTrue();
});