<?php

namespace BlazarRouter\Route\Exceptions;

use Monolog\Level;
use Stellar\Throwable\Exceptions\Contracts\Exception;
use Stellar\Throwable\Exceptions\Enum\ExceptionCode;
use Throwable;

class InvalidNumberOfArguments extends Exception
{
    public function __construct(string $route, ?Throwable $previous = null)
    {
        parent::__construct(
            "Invalid number of arguments on try define route $route.",
            ExceptionCode::DEVELOPER_EXCEPTION,
            Level::Error,
            $previous
        );
    }
}
