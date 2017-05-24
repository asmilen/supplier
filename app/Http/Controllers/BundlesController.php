<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Product;
use App\Models\BundleProduct;
use GuzzleHttp\Psr7\Request;

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
        $products = Product::where('status',1)->get();
        return view('bundles.create',compact('bundle','products'));
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
            'label' => 'required',
        ], [
            'name.required' => 'Hãy nhập tên nhóm sản phẩm.',
            'label.required' => 'Hãy chọn nhãn của nhóm sản phẩm.',
        ]);

        $bundle = Bundle::forceCreate([
            'name' => request('name'),
            'price' => (request('price')) ? request('price') : 0,
            'region_id' => request('region_id'),
            'label' => request('label'),
        ]);

        if (request()->has('productIds')) {

            $productsId = request('productIds');
            $quantity = request('quantity');

            for ($i = 0 ; $i< count($productsId); $i++)
            {
                $bundleProduct = BundleProduct::forceCreate([
                    'id_bundle' => $bundle->id,
                    'is_default' => ($productsId[$i] == request('default')) ? 1 : 0,
                    'id_product' => $productsId[$i],
                    'quantity' =>  isset($quantity[$i]) ? $quantity[$i] : 0
                ]);

            }

        }

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
        $bundleProducts = $bundle->products()->get();
        $productIds = $bundle->products()->pluck('products.id');
        $products = Product::where('status',1)->whereNotIn('id',$productIds)->get();
        return view('bundles.edit', compact('bundle','bundleProducts','products'));
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
        ], [
            'name.unique' => 'Hãy nhập tên nhóm sản phẩm.',
            'price.unique' => 'Hãy nhập giá nhóm sản phẩm.',
            'label.required' => 'Hãy chọn nhãn của nhóm sản phẩm.',
        ]);

        $bundle->forceFill([
            'name' => request('name'),
            'price' => (request('price')) ? request('price') : 0,
            'region_id' => request('region_id'),
            'label' => request('label'),
        ])->save();

        if (request()->has('productIds')) {

            $productsId = request('productIds');
            $quantity = request('quantity');
            for ($i = 0 ; $i< count($productsId); $i++)
            {
                $bundleProduct = BundleProduct::where('id_bundle',$bundle->id)->where('id_product',$productsId[$i])->first();

                if(count($bundleProduct) > 0)
                {
                    $bundleProduct->forceFill([
                        'is_default' => ($productsId[$i] == request('default')) ? 1 : 0,
                        'quantity' =>  isset($quantity[$i]) ? $quantity[$i] : 0
                    ])->save();
                } else
                {
                    $bundleProduct = BundleProduct::forceCreate([
                        'id_bundle' => $bundle->id,
                        'is_default' => ($productsId[$i] == request('default')) ? 1 : 0,
                        'id_product' => $productsId[$i],
                        'quantity' =>  isset($quantity[$i]) ? $quantity[$i] : 0
                    ]);
                }
            }

        }

        flash()->success('Success!', 'Bundle successfully updated.');

        return redirect()->route('bundles.index');
    }

    public function getDatatables()
    {
        return Bundle::getDatatables();
    }

}
