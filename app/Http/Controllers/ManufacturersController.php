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
            'name' => 'required|max:255',
            'code' => 'alpha_num|max:3|unique:manufacturers',
        ]);

        $manufacturer = Manufacturer::forceCreate([
            'name' => request('name'),
            'code' => strtoupper(request('code')),
            'homepage' => request('homepage'),
            'status' => !! request('status'),
        ]);

        if (empty($manufacturer->code)) {
            $manufacturer->forceFill([
                'code' => $manufacturer->id,
            ])->save();
        }

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
            'name' => 'required|max:255',
        ]);

        $manufacturer->forceFill([
            'name' => request('name'),
            'homepage' => request('homepage'),
            'status' => !! request('status'),
        ])->save();

        flash()->success('Success!', 'Manufacturer successfully updated.');

        return redirect()->route('manufacturers.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Manufacturer $manufacturer)
    {
        $manufacturer->delete();

        flash()->success('Success!', 'Manufacturer successfully deleted.');
    }

    public function getDatatables()
    {
        return Manufacturer::getDatatables();
    }
}
