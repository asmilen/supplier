<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Category;
use App\Models\BundleProduct;
use App\Models\BundleCategory;

class BundleProductsController extends Controller
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
        return view('bundleProducts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(BundleCategory $bundleCategory) {

        $categoriesID = json_decode($bundleCategory->category);
        $categories = Category::whereIn('id',$categoriesID)->get();
        $bundleProduct = (new BundleProduct)->forceFill([
            'is_default' => true,
        ]);

        return view('bundleProducts.create', compact('bundleCategory','categories','bundleProduct'));
    }

    public function store()
    {
        $productsId = request('id_product');

        foreach ($productsId as $id)
        {
            $bundleProduct = BundleProduct::forceCreate([
                'id_bundle' => request('id_bundle'),
                'id_bundleCategory' => request('id_bundleCategory'),
                'is_default' => request('is_default') ? request('is_default') : 0,
                'id_product' => $id,
                'quantity' => request('quantity') ? request('quantity') : 0
            ]);
        }

        flash()->success('Success!', 'Product successfully created.');

        return redirect()->route('bundleProducts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function show(BundleCategory $bundleCategory)
    {
        return view('bundleCategories.edit', compact('bundleCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function edit(BundleProduct $bundleProduct)
    {
//        $categories = json_decode($bundleCategory->category);
        return view('bundleProducts.edit', compact('bundleProduct'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function update(BundleProduct $bundleProduct)
    {
        $this->validate(request(), [
            'quantity' => 'required',
        ], [
            'quantity.required' => 'Hãy nhập số lượng sản phẩm.',
        ]);

        $bundleProduct->forceFill([
            'quantity' => request('quantity'),
        ])->save();

        flash()->success('Success!', 'Bundle Product successfully updated.');

        return redirect()->route('bundleProducts.index');
    }

    public function getDatatables()
    {
        return BundleProduct::getDatatables();
    }

}
