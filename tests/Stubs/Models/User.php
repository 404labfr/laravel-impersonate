<?php

namespace Lab404\Tests\Stubs\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    use Notifiable, Impersonate;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return  bool
     */
    public function canImpersonate()
    {
        return $this->attributes['is_admin'] == 1;
    }

    /*
     * @return bool
     */
    public function canBeImpersonated()
    {
        return $this->attributes['can_be_impersonated'] == 1;
    }
}
