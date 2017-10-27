<?php

namespace Lab404\Impersonate\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

abstract class ImpersonationException extends Exception
{
    /**
     * The model trying to impersonate another user.
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    public $impersonator;

    /**
     * The model another user is trying to impersonate.
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    public $impersonated;

    /**
     * Assigns the impersonator and the user impersonated to the exception.
     *
     * @param Model  $impersonator
     * @param Model  $impersonated
     */
    public function __construct (Model $impersonator, Model $impersonated, ... $args)
    {
        $this->impersonator = $impersonator;
        $this->impersonated = $impersonated;

        parent::__construct(... $args);
    }
}
