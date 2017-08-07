<?php

namespace App\Http\Controllers;

use App\Models\ModelTrackingLog;

class ModelTrackingLogsController extends Controller
{
    public function index()
    {
        $modelTypes = ModelTrackingLog::distinct('trackable_type')->pluck('trackable_type');

        return view('model-tracking-logs.index', compact('modelTypes'));
    }

    public function getDatatables()
    {
        return ModelTrackingLog::getDatatables();
    }
}
