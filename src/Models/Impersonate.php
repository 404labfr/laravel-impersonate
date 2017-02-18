<?php

namespace Lab404\Impersonate\Models;

use Illuminate\Database\Eloquent\Model;
use Lab404\Impersonate\Services\ImpersonateManager;

trait Impersonate
{
    /**
     * Return true or false if the user can impersonate an other user.
     *
     * @param   void
     * @return  bool
     */
    public function canImpersonate()
    {
        return true;
    }

    /**
     * Return true or false if the user can be impersonate.
     *
     * @param   Model $user
     * @return  bool
     */
    public function canBeImpersonate()
    {
        return true;
    }

    /**
     * Impersonate the given user.
     *
     * @param   Model $user
     * @return  bool
     */
    public function impersonate(Model $user)
    {
        if ($user->canBeImpersonate($user)) {
            return app(ImpersonateManager::class)->take($this, $user);
        } else {
            return false;
        }
    }

    /**
     * Check if the current user is impersonated.
     *
     * @param   void
     * @return  bool
     */
    public function isImpersonated()
    {
        return app(ImpersonateManager::class)->isImpersonating();
    }

    /**
     * Leave the current impersonation.
     *
     * @param   void
     * @return  bool
     */
    public function leaveImpersonation()
    {
        if ($this->isImpersonated())
        {
            return app(ImpersonateManager::class)->leave();
        }
    }
}
