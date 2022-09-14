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
        $manager = app()->make(ImpersonateManager::class);
        $guardName = $guardName ?? $manager->getDefaultSessionGuard();

        // Cannot impersonate yourself
        if ($id == $request->user()->getAuthIdentifier() && ($manager->getCurrentAuthGuardName() == $guardName)) {
            abort(403);
        }

        // Cannot impersonate again if you're already impersonate a user
        if ($manager->isImpersonating()) {
            abort(403);
        }

        if (!$request->user()->canImpersonate()) {
            abort(403);
        }

        $userToImpersonate = $manager->findUserById($id, $guardName);

        if ($userToImpersonate->canBeImpersonated()) {
            if ($manager->take($request->user(), $userToImpersonate, $guardName)) {
                $takeRedirect = $manager->getTakeRedirectTo();
                if ($takeRedirect !== 'back') {
                    return redirect()->to($takeRedirect);
                }
            }
        }

        return redirect()->back();
    }

    /**
     * @return RedirectResponse
     */
    public function leave()
    {
        $manager = app()->make(ImpersonateManager::class);
        if (!$manager->isImpersonating()) {
            abort(403);
        }

        $manager->leave();

        $leaveRedirect = $manager->getLeaveRedirectTo();
        if ($leaveRedirect !== 'back') {
            return redirect()->to($leaveRedirect);
        }
        return redirect()->back();
    }
}
