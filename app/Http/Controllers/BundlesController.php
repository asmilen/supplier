<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Bundle;
use App\Models\Product;
use App\Models\Province;
use App\Models\SupplierSupportedProvince;

class BundlesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bundles.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bundle = new Bundle;
        return view('bundles.create',compact('bundle'));
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
            'price' => 'required',
            'label' => 'required',
            'region_id' => 'required',
        ], [
            'name.required' => 'Hãy nhập tên nhóm sản phẩm.',
            'label.required' => 'Hãy chọn nhãn của nhóm sản phẩm.',
            'price.required' => 'Hãy nhập giá nhóm sản phẩm.',
            'region_id.required' => 'Hãy chọn vùng miền.',
        ]);

        $bundle = Bundle::forceCreate([
            'name' => request('name'),
            'price' => request('price', 0),
            'region_id' => request('region_id'),
            'label' => request('label'),
            'status' => request('status',false),
        ]);

        flash()->success('Success!', 'Bundle successfully created.');

        return redirect()->route('bundles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function show(Bundle $bundle)
    {
        return view('bundles.edit', compact('bundle'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function edit(Bundle $bundle)
    {
        return view('bundles.edit', compact('bundle'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function update(Bundle $bundle)
    {
        $this->validate(request(), [
            'name' => 'required|max:255',
            'price' => 'required|max:255',
            'label' => 'required',
            'region_id' => 'required',
        ], [
            'name.unique' => 'Hãy nhập tên nhóm sản phẩm.',
            'price.required' => 'Hãy nhập giá nhóm sản phẩm.',
            'label.required' => 'Hãy chọn nhãn của nhóm sản phẩm.',
            'region_id.required' => 'Hãy chọn vùng miền.',
        ]);

        $bundle->forceFill([
            'name' => request('name'),
            'price' => request('price', 0),
            'region_id' => request('region_id'),
            'label' => request('label'),
            'status' => request('status',false),
        ])->save();

        flash()->success('Success!', 'Bundle successfully updated.');

        return redirect()->route('bundles.index');
    }

    public function getDatatables()
    {
        return Bundle::getDatatables();
    }

    public function listProductByRegion(Bundle $bundle)
    {
        $productIds = [];
        $supplierIds = SupplierSupportedProvince::whereIn(
            'province_id', Province::getListByRegion($bundle->region_id)
        )->pluck('supplier_id')->all();

        if (request()->has('productIds')){
            $productIds = request('productIds');
        }
        
        return $bundle->listProductBySuppliers($supplierIds, $productIds, $bundle->region_id);
    }

    public function toggleStatus(Bundle $bundle) {
        $bundle->forceFill(['status' => ! $bundle->status])->save();
    }
}
