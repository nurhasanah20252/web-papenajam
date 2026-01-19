<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoring
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $next($request);

        $executionTime = (microtime(true) - $startTime) * 1000;
        $memoryUsage = (memory_get_usage(true) - $startMemory) / 1024 / 1024;

        if (config('app.debug') || config('app.env') === 'local') {
            $response->headers->set('X-Execution-Time', number_format($executionTime, 2).'ms');
            $response->headers->set('X-Memory-Usage', number_format($memoryUsage, 2).'MB');
        }

        if ($executionTime > 1000) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => number_format($executionTime, 2).'ms',
                'memory_usage' => number_format($memoryUsage, 2).'MB',
                'ip' => $request->ip(),
            ]);
        }

        if ($request->hasHeader('X-Query-Log')) {
            $queries = \DB::getQueryLog();
            $queryCount = count($queries);
            $response->headers->set('X-Query-Count', (string) $queryCount);

            if ($queryCount > 50) {
                Log::warning('High query count detected', [
                    'url' => $request->fullUrl(),
                    'query_count' => $queryCount,
                    'queries' => $queries,
                ]);
            }
        }

        return $response;
    }
}
