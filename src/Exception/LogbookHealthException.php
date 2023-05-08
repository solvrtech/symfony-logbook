<?php

namespace Solvrtech\Logbook\Exception;

use Exception;
use Throwable;

class LogbookHealthException extends Exception
{
    public function __construct(
        $message = "Health check was failed",
        $code = 500,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
