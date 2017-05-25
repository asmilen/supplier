<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class VersionController extends Controller
{
    public function index()
    {
        return api_response(
            [
                'environment' => env('SALE_TOOL_ENV', 'develop'),
            ], 200);
    }
}
