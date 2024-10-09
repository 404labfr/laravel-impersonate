<?php

namespace Lab404\Impersonate\Exceptions;

use Throwable;

class InvalidUserProvider extends \Exception
{
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link https://php.net/manual/en/exception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param null|Throwable  $previous [optional] The previous throwable used for the exception chaining.
     */
    public function __construct(string $guard, $message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Invalid user provider for guard %s', $guard), $code, $previous);
    }
}