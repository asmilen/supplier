<?php

namespace App\Http\Middleware;

use Log;
use Closure;
use App\Models\LogApi;

class LogAfterRequest
{
    protected $startTime;

    public function __construct() {
        $this->startTime = microtime(true);

    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
     public function handle($request, Closure $next)
     {
        return $next($request);
     }

     public function terminate($request, $response)
     {
         LogApi::forceCreate([
            'request' => $request,
            'response' => response()->json($response->getContent()),
            'devide' => $request->header('User-Agent'),
            'url' => $request->fullUrl(),
            'method' => $request->getMethod(),
            'ip_address' => $request->getClientIp(),
            'status' => $response->getStatusCode(),
         ]);
     }
}
