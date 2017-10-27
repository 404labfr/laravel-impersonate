<?php

namespace Lab404\Impersonate\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Lab404\Impersonate\Services\ImpersonateManager;
use Lab404\Impersonate\Exceptions\CannotImpersonateException;
use Lab404\Impersonate\Exceptions\CannotBeImpersonatedException;

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

        // Cannot impersonate again if you're already impersonating a user
        if ($this->manager->isImpersonating()) {
            abort(403);
        }

        $user_to_impersonate = $this->manager->findUserById($id);

        try {
            if ($this->manager->take($request->user(), $user_to_impersonate)) {
                $takeRedirect = $this->manager->getTakeRedirectTo();
                if ($takeRedirect !== 'back') {
                    return redirect()->to($takeRedirect);
                }
            }
        } catch (CannotImpersonateException $e) {
            abort(403);
        } catch (CannotBeImpersonatedException $e) {}

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
        if ($leaveRedirect !== 'back') {
            return redirect()->to($leaveRedirect);
        }
        return redirect()->back();
    }
}
