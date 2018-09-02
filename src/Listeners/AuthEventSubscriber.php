<?php

namespace Lab404\Impersonate\Listeners;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class AuthEventSubscriber extends ServiceProvider
{
    /**
     * Handle user login events.
     */
    public function onUserLogin($event) {
      dd('s');
    }

    /**
     * Handle user logout events.
     */
    public function onUserLogout($event) {
      dd('s');
    }

    protected $listen = [
          'Illuminate\Auth\Events\Login' => [
            'Lab404\Impersonate\Listeners\AuthEventSubscriber@onUserLogin',
          ],
          'Illuminate\Auth\Events\Logout' => [
              'Lab404\Impersonate\Listeners\AuthEventSubscriber@onUserLogin',
            ],
          ];



    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
dd('zzzz');
        $events->listen(
            'auth.login',
              'Lab404\Impersonate\Listeners\AuthEventSubscriber@onUserLogin'
        );

        $events->listen(
            'Illuminate\Auth\Events\Logout',
              'Lab404\Impersonate\Listeners\AuthEventSubscriber@onUserLogout'
        );
    }

}
