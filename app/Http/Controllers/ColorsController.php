<?php

namespace App\Http\Controllers;

use App\Models\Color;

class ColorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('colors.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $color = new Color;
        return view('colors.create',compact('color'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'name' => 'required|max:255|unique:colors',
            'code' => 'alpha_num|min:3|max:6|unique:colors',
        ], [
            'name.unique' => 'Hãy nhập tên màu sắc.',
            'code.unique' => 'Mã mau đã tồn tại.',
        ]);

        $color = Color::forceCreate([
            'name' => request('name'),
            'code' => request('code')
        ]);

        flash()->success('Success!', 'Color successfully created.');

        return redirect()->route('colors.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function show(Color $color)
    {
        return view('colors.edit', compact('color'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function edit(Color $color)
    {
        return view('colors.edit', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function update(Color $color)
    {
        $this->validate(request(), [
            'name' => 'required|max:255|unique:colors,name,'.$color->id,
        ], [
            'name.unique' => 'Màu sắc đã tồn tại.',
        ]);

        $color->forceFill([
            'name' => request('name'),
        ])->save();

        flash()->success('Success!', 'Color successfully updated.');

        return redirect()->route('colors.index');
    }

    public function getDatatables()
    {
        return Color::getDatatables();
    }
}
