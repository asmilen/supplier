<?php

namespace App\Http\Controllers;

use App\Models\Bundle;

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
            'price' => 'required|max:255',
        ], [
            'name.required' => 'Hãy nhập tên nhóm sản phẩm.',
            'price.required' => 'Hãy nhập giá nhóm sản phẩm.',
        ]);

        $bundle = Bundle::forceCreate([
            'name' => request('name'),
            'price' => request('price'),
        ]);

        flash()->success('Success!', 'Color successfully created.');

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
        ], [
            'name.unique' => 'Hãy nhập tên nhóm sản phẩm.',
            'price.unique' => 'Hãy nhập giá nhóm sản phẩm.',
        ]);

        $bundle->forceFill([
            'name' => request('name'),
            'price' => request('price'),
        ])->save();

        flash()->success('Success!', 'Color successfully updated.');

        return redirect()->route('bundles.index');
    }

    public function getDatatables()
    {
        return Bundle::getDatatables();
    }

    public function createCategory()
    {
        dd(1);
//        $bundle = Bundle::all();
//        return view('bundles.create',compact('bundle'));
    }
}
