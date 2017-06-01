<?php

namespace App\Http\Middleware;

use Log;
use Closure;
use Carbon\Carbon;

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
        $endtime = microtime(true);
        Log::info('ApiLog done===========================');
        Log::info('timeStart: '.$this->startTime);
        Log::info('timeEnd: '. $endtime);
        Log::info('responseTime: '. ($endtime - $this->startTime));
        Log::info('device: '.$request->header('User-Agent'));
        Log::info('URL: '.$request->fullUrl());
        Log::info('Method: '.$request->getMethod());
        Log::info('IP Address: '.$request->getClientIp());
        Log::info('Status Code: '.$response->getStatusCode());
        Log::info('Response: '.$response->getContent());
    }
}
