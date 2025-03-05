<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitTaskCreation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $cacheKey = "task_creation_limit_{$ip}";

        if (Cache::has(key: $cacheKey)) {
            return response()->json(data: ['message' => '请稍后再试，任务创建过于频繁！'], status: 429);
        }

        // 设置 5 秒的请求间隔
        Cache::put(key: $cacheKey, value: true, ttl: now()->addSeconds(value: 5));
        
        return $next($request);
    }
}
