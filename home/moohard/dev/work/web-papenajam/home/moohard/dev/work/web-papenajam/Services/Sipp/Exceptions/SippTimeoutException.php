<?php

namespace App\Services\Sipp\Exceptions;

class SippTimeoutException extends SippApiException
{
    public function __construct(string $message = '', int $attempt = 0, int $maxAttempts = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, 408, $attempt, $maxAttempts, $previous);
    }
}
