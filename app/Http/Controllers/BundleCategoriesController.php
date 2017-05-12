<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Category;
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
        return view('bundleCategories.create',compact('bundleCategory'));
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
            'category_id' => 'required',
            'bundle_id' => 'required',
        ], [
            'name.required' => 'Hãy nhập tên nhóm sản phẩm.',
            'category_id.required' => 'Hãy chọn danh mục.',
            'bundle_id.required' => 'Hãy chọn nhóm sản phẩm.',
        ]);

        $bundleCateogry = BundleCategory::forceCreate([
            'name' => request('name'),
            'id_bundle' => request('bundle_id'),
            'isRequired' => request('isRequired'),
            'category' => json_encode(request('category_id')),
        ]);

        flash()->success('Success!', 'Bundle Cateogry successfully created.');

        return redirect()->route('bundleCategories.index');
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
    public function edit(BundleCategory $bundleCategory)
    {
        $categories = json_decode($bundleCategory->category);
        return view('bundleCategories.edit', compact('bundleCategory','categories'));
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
            'category_id' => 'required',
            'bundle_id' => 'required',
        ], [
            'name.required' => 'Hãy nhập tên nhóm sản phẩm.',
            'category_id.required' => 'Hãy chọn danh mục.',
            'bundle_id.required' => 'Hãy chọn nhóm sản phẩm.',
        ]);


        $bundleCategory->forceFill([
            'name' => request('name'),
            'id_bundle' => request('bundle_id'),
            'isRequired' => request('isRequired'),
            'category' => json_encode(request('category_id')),
        ])->save();

        flash()->success('Success!', 'Bundle Category successfully updated.');

        return redirect()->route('bundleCategories.index');
    }

    public function getDatatables()
    {
        return BundleCategory::getDatatables();
    }

}
