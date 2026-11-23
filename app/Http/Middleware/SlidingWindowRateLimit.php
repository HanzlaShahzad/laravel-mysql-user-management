<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SlidingWindowRateLimit
{
  public function handle(Request $request, Closure $next)
  {
    $limit = 5;
    $time = 60;

    $now = (int) floor(microtime(true) * 1000);
    $WindowsMs = $time * 1000;

    $ip = $request->ip();
    $key = "Sliding-Window:user:{$ip}";

    $uniqueKey = (string) Str::uuid();
    Redis::zRemRangeByScore($key, '-inf', $now - $WindowsMs);

    $alreadyUser = Redis::zCard($key);
    if ($alreadyUser >= $limit) {
      return response()->json([
        'msg' => 'Too many requests',
        'limit' => $limit,
        'remaining' => 0,
      ], 429);
    }

    Redis::zadd($key, $now, $uniqueKey);
    Redis::expire($key, $time);

    return $next($request);
  }
}
