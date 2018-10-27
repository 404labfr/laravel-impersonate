<?php

namespace Lab404\Tests\Stubs\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Lab404\Impersonate\Models\Impersonate;

class AbsoluteAdmin extends Authenticatable
{
    use Notifiable, Impersonate;

    /**
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
    ];
}
