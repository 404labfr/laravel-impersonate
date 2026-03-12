<?php

namespace Lab404\Impersonate\Guard;

use Illuminate\Auth\SessionGuard as BaseSessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;

class SessionGuard extends BaseSessionGuard
{
    /**
     * Log a user into the application without firing the Login event.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function quietLogin(Authenticatable $user)
    {
        $this->updateSession($user->getAuthIdentifier());

        $this->updatePasswordHashes($user);

        $this->setUser($user);
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
        $this->clearUserDataFromStorage();

        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * Removes the stored password hashes from the session.
     *
     * @param   void
     * @return  void
     */
    protected function updatePasswordHashes(Authenticatable $user)
    {
        // Sort out password hashes stored in session
        foreach (array_keys(config('auth.guards')) as $guard) {
            $hashName = 'password_hash_' . $guard;
            if ($this->session->has($hashName)) {
                $this->session->put($hashName, $user->getAuthPassword());
            }
        }
    }
}
