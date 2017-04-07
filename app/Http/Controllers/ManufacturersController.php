<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;

class ManufacturersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('manufacturers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $manufacturer = (new Manufacturer)->forceFill([
            'status' => true,
        ]);

        return view('manufacturers.create', compact('manufacturer'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'name' => 'required|max:255|unique:manufacturers',
            'code' => 'alpha_num|min:3|max:6|unique:manufacturers',
        ], [
            'name.unique' => 'Tên nhà sản xuất đã tồn tại.',
            'code.unique' => 'Mã nhà sản xuất đã tồn tại.',
        ]);

        $manufacturer = Manufacturer::forceCreate([
            'name' => request('name'),
            'code' => strtoupper(request('code')),
            'homepage' => request('homepage'),
            'status' => !! request('status'),
        ]);

        flash()->success('Success!', 'Manufacturer successfully created.');

        return redirect()->route('manufacturers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function show(Manufacturer $manufacturer)
    {
        return view('manufacturers.edit', compact('manufacturer'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function edit(Manufacturer $manufacturer)
    {
        return view('manufacturers.edit', compact('manufacturer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function update(Manufacturer $manufacturer)
    {
        $this->validate(request(), [
            'name' => 'required|max:255|unique:manufacturers,name,'.$manufacturer->id,
        ], [
            'name.unique' => 'Tên nhà sản xuất đã tồn tại.',
        ]);

        $manufacturer->forceFill([
            'name' => request('name'),
            'homepage' => request('homepage'),
            'status' => !! request('status'),
        ])->save();

        flash()->success('Success!', 'Manufacturer successfully updated.');

        return redirect()->route('manufacturers.index');
    }

    public function getDatatables()
    {
        return Manufacturer::getDatatables();
    }
}
