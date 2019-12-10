<?php

namespace Lab404\Impersonate\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TakeImpersonation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  Authenticatable */
    public $impersonator;

    /** @var  Authenticatable */
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
