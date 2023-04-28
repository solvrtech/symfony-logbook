<?php

namespace Solvrtech\Logbook\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class LogbookSecurityException extends HttpException
{
    public function __construct(
        $message = "An authentication exception occurred",
        $code = 401,
        Throwable $previous = null
    ) {
        parent::__construct($code, $message, $previous);
    }
}