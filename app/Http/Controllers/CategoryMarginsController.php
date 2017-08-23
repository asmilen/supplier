<?php

namespace App\Http\Controllers;

use App\Jobs\UpdatePriceToMagento;
use App\Models\Category;
use App\Models\MarginRegionCategory;
use Sentinel;

class CategoryMarginsController extends Controller
{
    public function index(Category $category)
    {
        return $category->margins->keyBy('region_id');
    }

    public function update(Category $category)
    {
        $this->validate(request(), [
            'north_region' => 'required|numeric|min:1|max:100',
            'middle_region' => 'required|numeric|min:1|max:100',
            'south_region' => 'required|numeric|min:1|max:100',
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

        dispatch(new UpdatePriceToMagento(Sentinel::getUser()->id, 0));
    }
}
