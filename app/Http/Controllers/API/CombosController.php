<?php

namespace App\Http\Controllers\API;

use DB;
use Validator;
use Datatables;
use App\Models\Combo;
use App\Http\Controllers\Controller;

class CombosController extends Controller
{
    public function index()
    {
        $model = Combo::select('id', 'name', 'price', 'code');

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('name')) {
                    $query->where('combos.name', 'like', '%' . request('name') . '%');
                }
            })
            ->make(true);
    }

    public function detail(Combo $combo)
    {
        return $combo->products;
    }
}
