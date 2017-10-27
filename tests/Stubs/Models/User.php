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
    public function canImpersonate($impersonate_whom)
    {
        // Superadmins cannot be impersonated, so check for that.
        return $this->role !== 'user' && in_array($impersonate_whom->role, ['user', 'manager', 'admin']);
    }

    /*
     * @return bool
     */
    public function canBeImpersonated($impersonated_by)
    {
        if ($this->role === 'user') {
            return true;
        }

        if ($this->role === 'manager') {
            return in_array($impersonated_by, ['admin', 'superadmin']);
        }

        if ($this->role === 'admin') {
            return $impersonated_by->role === 'superadmin';
        }

        return false;
    }
}
