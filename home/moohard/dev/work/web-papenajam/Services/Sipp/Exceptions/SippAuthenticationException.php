<?php

namespace App\Services\Sipp\Exceptions;

class SippAuthenticationException extends SippApiException
{
    public function __construct(string $message = '', int $attempt = 0, int $maxAttempts = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, 401, $attempt, $maxAttempts, $previous);
    }
}
