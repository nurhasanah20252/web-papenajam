<?php

namespace App\Services\Sipp;

use App\Services\Sipp\Exceptions\SippApiException;
use App\Services\Sipp\Exceptions\SippAuthenticationException;
use App\Services\Sipp\Exceptions\SippRateLimitException;
use App\Services\Sipp\Exceptions\SippTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;

class SippApiClient
{
    protected string $baseUrl;

    protected string $apiKey;

    protected ?string $bearerToken;

    protected int $timeout;

    protected int $retryAttempts;

    protected int $retryDelay;

    protected int $rateLimitRequests;

    protected int $rateLimitWindow;

    protected array $lastRateLimitInfo = [];

    private const CACHE_TTL_SCHEDULES = 3600; // 1 hour

    private const CACHE_TTL_CASES = 7200; // 2 hours

    private const CACHE_TTL_STATISTICS = 86400; // 24 hours

    public function __construct()
    {
        $this->baseUrl = config('sipp.api.base_url', '');
        $this->apiKey = config('sipp.api.api_key', '');
        $this->bearerToken = config('sipp.api.bearer_token');
        $this->timeout = config('sipp.api.timeout', 30);
        $this->retryAttempts = config('sipp.api.retry_attempts', 3);
        $this->retryDelay = config('sipp.api.retry_delay', 1000);
        $this->rateLimitRequests = config('sipp.api.rate_limit_requests', 100);
        $this->rateLimitWindow = config('sipp.api.rate_limit_window', 60);
    }

    /**
     * Make HTTP request with retry logic and rate limiting.
     *
     * @throws SippApiException
     */
    public function request(string $method, string $endpoint, array $data = [], bool $useCache = false): array
    {
        $cacheKey = $this->getCacheKey($method, $endpoint, $data);

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $this->checkRateLimit();

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = $this->makeRequest($method, $endpoint, $data);

                $this->updateRateLimitInfo($response);

                if ($useCache) {
                    $cacheTtl = $this->getCacheTtl($endpoint);
                    Cache::put($cacheKey, $response, $cacheTtl);
                }

                return $response;
            } catch (ConnectionException $e) {
                $lastException = new SippTimeoutException(
                    "Connection failed: {$e->getMessage()}",
                    $attempt + 1,
                    $this->retryAttempts
                );
            } catch (RequestException $e) {
                $lastException = $this->handleRequestException($e, $attempt);
            } catch (\Exception $e) {
                $lastException = new SippApiException(
                    "Request failed: {$e->getMessage()}",
                    $e->getCode(),
                    $attempt + 1,
                    $this->retryAttempts
                );
            }

            $attempt++;
            $this->handleRetryDelay($attempt, $lastException);
        }

        throw $lastException;
    }

    /**
     * Get schedules from SIPP API.
     *
     * @throws SippApiException
     */
    public function getSchedules(array $filters = [], bool $useCache = true): array
    {
        $params = array_merge([
            'page' => 1,
            'limit' => 100,
        ], $filters);

        return $this->request('GET', '/schedules', $params, $useCache);
    }

    /**
     * Get single schedule details.
     *
     * @throws SippApiException
     */
    public function getSchedule(string $id, bool $useCache = true): array
    {
        return $this->request('GET', "/schedules/{$id}", [], $useCache);
    }

    /**
     * Get cases from SIPP API.
     *
     * @throws SippApiException
     */
    public function getCases(array $filters = [], bool $useCache = true): array
    {
        $params = array_merge([
            'page' => 1,
            'limit' => 100,
        ], $filters);

        return $this->request('GET', '/cases', $params, $useCache);
    }

    /**
     * Get single case details.
     *
     * @throws SippApiException
     */
    public function getCase(string $id, bool $useCache = true): array
    {
        return $this->request('GET', "/cases/{$id}", [], $useCache);
    }

    /**
     * Get case statistics.
     *
     * @throws SippApiException
     */
    public function getStatistics(array $filters = [], bool $useCache = true): array
    {
        $params = array_merge([
            'year' => now()->year,
            'month' => now()->month,
        ], $filters);

        return $this->request('GET', '/statistics', $params, $useCache);
    }

    /**
     * Get judges list.
     *
     * @throws SippApiException
     */
    public function getJudges(bool $useCache = true): array
    {
        return $this->request('GET', '/judges', [], $useCache);
    }

    /**
     * Get court rooms list.
     *
     * @throws SippApiException
     */
    public function getCourtRooms(bool $useCache = true): array
    {
        return $this->request('GET', '/court-rooms', [], $useCache);
    }

    /**
     * Get case types list.
     *
     * @throws SippApiException
     */
    public function getCaseTypes(bool $useCache = true): array
    {
        return $this->request('GET', '/case-types', [], $useCache);
    }

    /**
     * Make the HTTP request.
     */
    protected function makeRequest(string $method, string $endpoint, array $data): array
    {
        $url = rtrim($this->baseUrl, '/').$endpoint;

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'PA-Penajam-SIPP-Client/1.0',
        ];

        if ($this->apiKey) {
            $headers['X-API-Key'] = $this->apiKey;
        }

        if ($this->bearerToken) {
            $headers['Authorization'] = 'Bearer '.$this->bearerToken;
        }

        $options = [
            'headers' => $headers,
            'timeout' => $this->timeout,
            'connect_timeout' => 10,
        ];

        if (! empty($data) && in_array(strtoupper($method), ['GET'])) {
            $options['query'] = $data;
        } elseif (! empty($data) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $options['json'] = $data;
        }

        $response = Http::withOptions($options)
            ->send($method, $url);

        if ($response->failed()) {
            throw new RequestException($response->toPsrResponse());
        }

        return $response->json() ?? [];
    }

    /**
     * Handle request exception and convert to appropriate SIPP exception.
     */
    protected function handleRequestException(RequestException $e, int $attempt): SippApiException
    {
        $statusCode = $e->getCode();
        $message = $e->getMessage();

        return match ($statusCode) {
            401, 403 => new SippAuthenticationException(
                "Authentication failed: {$message}",
                $attempt + 1,
                $this->retryAttempts
            ),
            429 => new SippRateLimitException(
                "Rate limit exceeded: {$message}",
                $attempt + 1,
                $this->retryAttempts
            ),
            408 => new SippTimeoutException(
                "Request timeout: {$message}",
                $attempt + 1,
                $this->retryAttempts
            ),
            default => new SippApiException(
                "API request failed with status {$statusCode}: {$message}",
                $statusCode,
                $attempt + 1,
                $this->retryAttempts
            ),
        };
    }

    /**
     * Check rate limit before making request.
     *
     * @throws SippRateLimitException
     */
    protected function checkRateLimit(): void
    {
        $key = 'sipp_rate_limit_count';

        $current = Cache::get($key, 0);

        if ($current >= $this->rateLimitRequests) {
            $ttl = Cache::ttl($key);

            throw new SippRateLimitException(
                "Rate limit exceeded. Retry after {$ttl} seconds.",
                0,
                $this->retryAttempts,
                $ttl
            );
        }

        Cache::increment($key);

        if ($current === 0) {
            Cache::put($key, $current + 1, $this->rateLimitWindow);
        }
    }

    /**
     * Update rate limit info from response headers.
     *
     * @param  \Illuminate\Http\Client\Response  $response
     */
    protected function updateRateLimitInfo($response): void
    {
        $this->lastRateLimitInfo = [
            'limit' => $response->header('X-RateLimit-Limit'),
            'remaining' => $response->header('X-RateLimit-Remaining'),
            'reset' => $response->header('X-RateLimit-Reset'),
        ];
    }

    /**
     * Get last rate limit info.
     */
    public function getLastRateLimitInfo(): array
    {
        return $this->lastRateLimitInfo;
    }

    /**
     * Handle retry delay between attempts.
     */
    protected function handleRetryDelay(int $attempt, \Exception $exception): void
    {
        $delay = $this->retryDelay * $attempt;

        Log::warning('SIPP API retry', [
            'attempt' => $attempt,
            'max_attempts' => $this->retryAttempts,
            'delay_ms' => $delay,
            'exception' => $exception->getMessage(),
        ]);

        usleep($delay * 1000);
    }

    /**
     * Generate cache key for request.
     */
    protected function getCacheKey(string $method, string $endpoint, array $params): string
    {
        $hash = md5($method.$endpoint.json_encode($params));

        return 'sipp_api_'.Str::slug($endpoint, '_').'_'.$hash;
    }

    /**
     * Get cache TTL based on endpoint.
     */
    protected function getCacheTtl(string $endpoint): int
    {
        return match (true) {
            str_contains($endpoint, '/statistics') => self::CACHE_TTL_STATISTICS,
            str_contains($endpoint, '/cases') => self::CACHE_TTL_CASES,
            str_contains($endpoint, '/schedules') => self::CACHE_TTL_SCHEDULES,
            default => self::CACHE_TTL_SCHEDULES,
        };
    }

    /**
     * Clear cache for specific endpoint.
     *
     * @throws InvalidArgumentException
     */
    public function clearCache(string $endpoint): bool
    {
        $pattern = 'sipp_api_'.Str::slug($endpoint, '_').'*';

        Cache::flush();

        Log::info('SIPP API cache cleared', ['endpoint' => $endpoint]);

        return true;
    }

    /**
     * Check if API is available.
     */
    public function isAvailable(): bool
    {
        try {
            $this->request('GET', '/health', [], false);

            return true;
        } catch (\Exception $e) {
            Log::warning('SIPP API availability check failed', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get API base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Check if client is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl) && (! empty($this->apiKey) || ! empty($this->bearerToken));
    }
}
