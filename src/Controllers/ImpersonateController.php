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
        if ($id == $request->user()->id)
        {
            abort(403);
        }

        // Cannot impersonate again if you're already impersonate a user
        if ($this->manager->isImpersonating())
        {
            abort(403);
        }

        if ($this->manager->take($request->user(), $this->manager->findUserById($id)))
        {
            return redirect()->to($this->manager->getTakeRedirectTo());
        }

        return redirect()->back();
    }

    /*
     * @return RedirectResponse
     */
    public function leave()
    {
        if (!$this->manager->isImpersonating())
        {
            abort(403);
        }

        
        $this->manager->leave();

        return redirect()->to($this->manager->getLeaveRedirectTo());
    }
}
