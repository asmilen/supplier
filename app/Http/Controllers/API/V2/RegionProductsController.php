<?php

namespace App\Http\Controllers\API\V2;

use App\Models\Product;
use App\Http\Controllers\Controller;

class RegionProductsController extends Controller
{
    public function index()
    {
        if (! $this->isValidRegionId()) {
            return response([
                'error' => 'Thiếu mã region_id hoặc mã region_id không hợp lệ.',
            ], 422);
        }

        $sorting = request('sorting', 'code');

        $direction = request('direction', 'asc');

        $page = request('page', 1);

        $limit = request('limit', 100);

        $builder = Product::with('category', 'manufacturer')
            ->active()
            ->where(function ($query) {
                if (! empty(request('ids'))) {
                    $query->whereIn('id', explode(',', request('ids')));
                }
            })
            ->whereHas('productSuppliers', function ($query) {
                $query->where('region_id', request('region_id', 0));
            });

        $totalItems = $builder->count();

        $products = $builder
            ->orderBy($sorting, $direction)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return response([
            'data' => $products,
            'total_items' => $totalItems,
        ]);
    }

    protected function isValidRegionId()
    {
        return in_array(request('region_id', 0), [1, 2, 3]);
    }
}
