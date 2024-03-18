<?php

namespace Lab404\Impersonate\Guard;

use Illuminate\Contracts\Auth\Authenticatable;

class SessionGuardMixin
{
    /**
     * Log a user into the application without firing the Login event.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function quietLogin()
    {
        return function (Authenticatable $user) {
            $this->updateSession($user->getAuthIdentifier());

            $this->setUser($user);
        };
    }

    /**
     * Logout the user without updating remember_token
     * and without firing the Logout event.
     *
     * @param   void
     * @return  void
     */
    public function quietLogout()
    {
        return function () {
            $this->clearUserDataFromStorage();

            $this->user = null;

            $this->loggedOut = true;
        };
    }
}
