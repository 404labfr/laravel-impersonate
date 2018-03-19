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
        $this->middleware('auth');

        $this->manager = app()->make(ImpersonateManager::class);
    }

    /**
     * @param   int $id
     * @return  RedirectResponse
     */
    public function take(Request $request, $id)
    {
        // Cannot impersonate yourself
        if ($id == $request->user()->getKey()) {
            abort(403);
        }

        // Cannot impersonate again if you're already impersonate a user
        if ($this->manager->isImpersonating()) {
            abort(403);
        }

        if (!$request->user()->canImpersonate()) {
            abort(403);
        }
        
        // save the origin when set in config
        if ($this->manager->getLeaveRedirectTo() === 'origin') {
            session()->put('impersonate_origin', $request->header('referer'));
        }

        $user_to_impersonate = $this->manager->findUserById($id);

        if ($user_to_impersonate->canBeImpersonated()) {
            if ($this->manager->take($request->user(), $user_to_impersonate)) {
                $takeRedirect = $this->manager->getTakeRedirectTo();
                if ($takeRedirect !== 'back') {
                    return redirect()->to($takeRedirect);
                }
            }
        }

        return redirect()->back();
    }

    /*
     * @return RedirectResponse
     */
    public function leave()
    {
        if (!$this->manager->isImpersonating()) {
            abort(403);
        }

        $this->manager->leave();

        $leaveRedirect = $this->manager->getLeaveRedirectTo();
        
        // redirect back to origin when set and when session available
        if ($leaveRedirect === 'origin' && session()->get('impersonate_origin')) {
            return redirect()->to(session()->get('impersonate_origin'));
        }
        
        if ($leaveRedirect !== 'back') {
            return redirect()->to($leaveRedirect);
        }
        return redirect()->back();
    }
}
