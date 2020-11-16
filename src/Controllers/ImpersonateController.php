<?php

namespace Lab404\Impersonate\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lab404\Impersonate\Services\ImpersonateManager;

class ImpersonateController extends Controller
{
    /** @var ImpersonateManager */
    protected $manager;

    /**
     * ImpersonateController constructor.
     */
    public function __construct()
    {
        $this->manager = app()->make(ImpersonateManager::class);
        
        $guard = $this->manager->getDefaultSessionGuard();
        $this->middleware('auth:' . $guard)->only('take');
    }

    /**
     * @param int         $id
     * @param string|null $guardName
     * @return  RedirectResponse
     * @throws  \Exception
     */
    public function take(Request $request, $id, $guardName = null)
    {
        $guardName = $guardName ?? $this->manager->getDefaultSessionGuard();

        // Cannot impersonate yourself
        if ($id == $request->user()->getAuthIdentifier() && ($this->manager->getCurrentAuthGuardName() == $guardName)) {
            abort(403, 'You cannot impersonate yourself.');
        }

        // Cannot impersonate again if you're already impersonate a user
        if ($this->manager->isImpersonating()) {
            abort(403, 'You are already impersonating a user.');
        }

        if (!$request->user()->canImpersonate()) {
            abort(403, 'You cannot impersonate users.');
        }

        $userToImpersonate = $this->manager->findUserById($id, $guardName);

        if (!$userToImpersonate->canBeImpersonated()) {
            abort(403, 'User cannot be impersonated.');
        }

        if ($this->manager->take($request->user(), $userToImpersonate, $guardName)) {
            $takeRedirect = $this->manager->getTakeRedirectTo();
            if ($takeRedirect === 'back') {
                return redirect()->back();
            }
            return redirect()->to($takeRedirect);
        }

        abort(403, 'Impersonation failed.');
    }

    /**
     * @return RedirectResponse
     */
    public function leave()
    {
        if (!$this->manager->isImpersonating()) {
            abort(403, 'You are not currently impersonating any user.');
        }

        $this->manager->leave();

        $leaveRedirect = $this->manager->getLeaveRedirectTo();
        if ($leaveRedirect !== 'back') {
            return redirect()->to($leaveRedirect);
        }
        return redirect()->back();
    }
}
