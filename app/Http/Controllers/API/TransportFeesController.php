<?php

namespace App\Http\Controllers\API;

use App\Models\TransportFee;
use App\Http\Controllers\Controller;

class TransportFeesController extends Controller
{
    public function index()
    {
        return TransportFee::getList();
    }
}
