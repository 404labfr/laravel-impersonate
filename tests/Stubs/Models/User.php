<?php

namespace Tests\Stubs\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    use Impersonate;
    use Notifiable;

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
    public function canImpersonate(): bool
    {
        return $this->attributes['is_admin'] == 1;
    }

    /*
     * @return bool
     */
    public function canBeImpersonated(): bool
    {
        return $this->attributes['can_be_impersonated'] == 1;
    }


    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return 'email';
    }
}
