<?php

namespace App\Services\Sipp\Exceptions;

class SippRateLimitException extends SippApiException
{
    protected int $retryAfter;

    public function __construct(string $message = '', int $attempt = 0, int $maxAttempts = 0, int $retryAfter = 0, ?\Throwable $previous = null)
    {
        $this->retryAfter = $retryAfter;

        parent::__construct($message, 429, $attempt, $maxAttempts, $previous);
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'retry_after' => $this->retryAfter,
        ]);
    }
}
