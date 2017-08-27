<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MarginRegionCategory;
use App\Jobs\UpdateCategoryPriceToMagento;

class CategoryMarginsController extends Controller
{
    public function index(Category $category)
    {
        return $category->margins->keyBy('region_id');
    }

    public function update(Category $category)
    {
        $this->validate(request(), [
            'north_region' => 'required|numeric|min:0.1|max:100',
            'middle_region' => 'required|numeric|min:0.1|max:100',
            'south_region' => 'required|numeric|min:0.1|max:100',
        ], [
            'north_region.required' => 'Vui lòng nhập margin.',
            'north_region.max' => 'Margin quá lớn, tối đa 100.',
            'north_region.min' => 'Margin phải lớn hơn 0.',
            'north_region.numeric' => 'Margin phải là số.',
            'middle_region.required' => 'Vui lòng nhập margin.',
            'middle_region.max' => 'Margin quá lớn, tối đa 100.',
            'middle_region.min' => 'Margin phải lớn hơn 0.',
            'middle_region.numeric' => 'Margin phải là số.',
            'south_region.required' => 'Vui lòng nhập margin.',
            'south_region.max' => 'Margin quá lớn, tối đa 100.',
            'south_region.min' => 'Margin phải lớn hơn 0.',
            'south_region.numeric' => 'Margin phải là số.',
        ]);

        $mapToRegions = [
            'north_region' => 1,
            'middle_region' => 2,
            'south_region' => 3,
        ];

        foreach (request()->only(['north_region', 'middle_region', 'south_region']) as $regionVar => $value) {
            MarginRegionCategory::updateOrCreate([
                'region_id' => $mapToRegions[$regionVar],
                'category_id' => $category->id,
            ], [
                'margin' => $value,
            ]);
        }

        dispatch(new UpdateCategoryPriceToMagento($category));
    }
}
