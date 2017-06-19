<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MarginRegionCategory;

class CategoryMarginsController extends Controller
{
    public function index(Category $category)
    {
        return $category->margins->keyBy('region_id');
    }

    public function update(Category $category)
    {
        $this->validate(request(), [
            'north_region' => 'required|numeric|min:0|max:100',
            'middle_region' => 'required|numeric|min:0|max:100',
            'south_region' => 'required|numeric|min:0|max:100',
        ]);

        $mapToRegions = [
            'north_region' => 1,
            'middle_region' => 2,
            'south_region' => 3,
        ];

        foreach (request()->all() as $regionVar => $value) {
            $regionId = $mapToRegions[$regionVar];

            $marginRegionCategory = MarginRegionCategory::where('category_id', $category->id)
                ->where('region_id', $regionId)
                ->first();

            if (! $marginRegionCategory) {
                $marginRegionCategory = (new MarginRegionCategory)->forceFill([
                    'category_id' => $category->id,
                    'region_id' => $regionId,
                ]);
            }

            $marginRegionCategory->forceFill(['margin' => $value])->save();
        }
    }
}
