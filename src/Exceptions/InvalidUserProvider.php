<?php

namespace Lab404\Impersonate\Exceptions;

use Throwable;

class InvalidUserProvider extends \Exception
{
    public function __construct(string $guard, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Invalid user provider for guard %s', $guard), $code, $previous);
    }
}