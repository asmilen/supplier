<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Product;
use App\Models\Category;
use App\Models\Province;
use App\Models\BundleProduct;
use App\Models\BundleCategory;
use App\Models\SupplierSupportedProvince;

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

        $category = Product::where('id', request('default'))->first();

        $bundleCategory = BundleCategory::forceCreate([
            'name' => request('name'),
            'id_bundle' => request('bundle_id'),
            'category' => $category ? $category->category_id : 0,
            'isRequired' => request('isRequired', 0),
            'status' => request('status',false),
        ]);

        if (request()->has('productIds')) {
            $productIds = request('productIds');

            $quantity = request('quantity');

            foreach ($productIds as $key => $productId) {
                $bundleProduct = BundleProduct::forceCreate([
                    'id_bundle' => request('bundle_id'),
                    'id_bundleCategory' => $bundleCategory->id,
                    'is_default' => $productId == request('default'),
                    'id_product' => $productIds[$key],
                    'quantity' =>  isset($quantity[$key]) ? $quantity[$key] : 0,
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
        $supplierIds = SupplierSupportedProvince::whereIn(
            'province_id', Province::getListByRegion($bundleCategory->bundle()->pluck('region_id'))
        )->pluck('supplier_id')->all();

        $bundleProducts = $bundleCategory->listProducts($supplierIds, $bundleCategory->id, $bundleCategory->bundle()->pluck('region_id'));

        $products = $bundleCategory->listProductBySuppliersNotExist($supplierIds, $bundleCategory->products()->pluck('products.id'), $bundleCategory->bundle()->pluck('region_id'));

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

        $category = Product::where('id', request('default'))->first();

        $bundleCategory->forceFill([
            'name' => request('name'),
            'id_bundle' => request('bundle_id'),
            'category' => $category ? $category->category_id : 0,
            'isRequired' => request('isRequired', 0),
            'status' => request('status',false),
        ])->save();

        if (request()->has('productIds')) {
            $productIds = request('productIds');
            $quantity = request('quantity');

            foreach ($productIds as $key => $productId) {
                $bundleProduct = BundleProduct::where('id_bundle',request('bundle_id'))
                    ->where('id_product',$productId)
                    ->where('id_bundleCategory',$bundleCategory->id)
                    ->first();

                if (! $bundleProduct) {
                    $bundleProduct = (new BundleProduct)->forceFill([
                        'id_bundle' => request('bundle_id'),
                        'id_bundleCategory' => $bundleCategory->id,
                        'id_product' => $productId,
                    ]);
                }

                $bundleProduct->forceFill([
                    'is_default' => $productId == request('default'),
                    'quantity' => isset($quantity[$key]) ? $quantity[$key] : 1
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

    public function toggleStatus(BundleCategory $bundleCategory) {
        $bundleCategory->forceFill(['status' => ! $bundleCategory->status])->save();
    }
}
