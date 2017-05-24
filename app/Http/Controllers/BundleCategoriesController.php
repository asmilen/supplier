<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Product;
use App\Models\Category;
use App\Models\BundleProduct;
use App\Models\BundleCategory;

class BundleCategoriesController extends Controller
{
    public function __construct()
    {
        view()->share('categoriesList', Category::getActiveList());
        view()->share('bundlesList', Bundle::getActiveList());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bundleCategories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bundleCategory = (new BundleCategory)->forceFill([
            'isRequired' => true,
        ]);

        $products = Product::active()->get();

        return view('bundleCategories.create', compact('bundleCategory','products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'name' => 'required|max:255',
            'bundle_id' => 'required',
        ], [
            'name.required' => 'Hãy nhập tên nhóm sản phẩm.',
            'bundle_id.required' => 'Hãy chọn nhóm sản phẩm.',
        ]);

        $bundleCategory = BundleCategory::forceCreate([
            'name' => request('name'),
            'id_bundle' => request('bundle_id'),
            'isRequired' => request('isRequired'),
        ]);

        if (request()->has('productIds')) {
            $productIds = request('productIds');

            $quantity = request('quantity');

            for ($i = 0; $i < count($productIds); $i++) {
                $bundleProduct = BundleProduct::forceCreate([
                    'id_bundle' => request('bundle_id'),
                    'id_bundleCategory' => $bundleCategory->id,
                    'is_default' => $productIds[$i] == request('default'),
                    'id_product' => $productIds[$i],
                    'quantity' =>  isset($quantity[$i]) ? $quantity[$i] : 0,
                ]);
            }
        }

        flash()->success('Success!', 'Bundle Category successfully created.');

        return redirect()->route('bundleCategories.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function edit(BundleCategory $bundleCategory)
    {
        $bundleProducts = $bundleCategory->products()->get();

        $productIds = $bundleCategory->products()->pluck('products.id');

        $products = Product::where('status',true)->whereNotIn('id',$productIds)->get();

        return view('bundleCategories.edit', compact('bundleCategory', 'bundleProducts', 'products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function update(BundleCategory $bundleCategory)
    {
        $this->validate(request(), [
            'name' => 'required|max:255',
            'bundle_id' => 'required',
        ], [
            'name.required' => 'Hãy nhập tên nhóm sản phẩm.',
            'bundle_id.required' => 'Hãy chọn nhóm sản phẩm.',
        ]);

        $bundleCategory->forceFill([
            'name' => request('name'),
            'id_bundle' => request('bundle_id'),
            'isRequired' => request('isRequired'),
        ])->save();

        if (request()->has('productIds')) {
            $productIds = request('productIds');
            $quantity = request('quantity');
            for ($i = 0; $i < count($productIds); $i++) {
                $bundleProduct = BundleProduct::where('id_bundle',request('bundle_id'))
                    ->where('id_product',$productIds[$i])
                    ->first();

                if (! $bundleProduct) {
                    $bundleProduct = (new BundleProduct)->forceFill([
                        'id_bundle' => request('bundle_id'),
                        'id_bundleCategory' => $bundleCategory->id,
                        'id_product' => $productIds[$i],
                    ]);
                }

                $bundleProduct->forceFill([
                    'is_default' => $productIds[$i] == request('default'),
                    'quantity' => isset($quantity[$i]) ? $quantity[$i] : 0
                ])->save();
            }
        }

        flash()->success('Success!', 'Bundle Category successfully updated.');

        return redirect()->route('bundleCategories.index');
    }

    public function getDatatables()
    {
        return BundleCategory::getDatatables();
    }
}
