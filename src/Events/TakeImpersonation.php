<?php

namespace Lab404\Impersonate\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TakeImpersonation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  Model */
    public $impersonator;

    /** @var  Model */
    public $impersonated;

    /**
     * Create a new event instance.
     *
     * @return  void
     */
    public function __construct(Model $impersonator, Model $impersonated)
    {
        $this->impersonator = $impersonator;
        $this->impersonated = $impersonated;
    }
}
