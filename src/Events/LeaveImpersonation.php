<?php

namespace Lab404\Impersonate\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class LeaveImpersonation
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var Authenticatable */
    public $impersonator;

    /** @var Authenticatable */
    public $impersonated;

    /**
     * Create a new event instance.
     *
     * @return  void
     */
    public function __construct(Authenticatable $impersonator, Authenticatable $impersonated)
    {
        $this->impersonator = $impersonator;
        $this->impersonated = $impersonated;
    }
}
