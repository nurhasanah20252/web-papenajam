<?php

namespace App\Services\Sipp\Exceptions;

use Exception;

class SippApiException extends Exception
{
    protected int $attempt;

    protected int $maxAttempts;

    public function __construct(string $message = '', int $code = 0, int $attempt = 0, int $maxAttempts = 0, ?\Throwable $previous = null)
    {
        $this->attempt = $attempt;
        $this->maxAttempts = $maxAttempts;

        parent::__construct($message, $code, $previous);
    }

    public function getAttempt(): int
    {
        return $this->attempt;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function hasRetriesLeft(): bool
    {
        return $this->attempt < $this->maxAttempts;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'attempt' => $this->attempt,
            'max_attempts' => $this->maxAttempts,
            'has_retries_left' => $this->hasRetriesLeft(),
        ];
    }
}
