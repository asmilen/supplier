<?php

namespace App\Http\Controllers;

use App\Models\Margin;

class MarginsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('margins.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $margin = new Margin;
        
        return view('margins.create',compact('margin'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'label' => 'required',
            'value' => 'required',
        ], [
            'label.required' => 'Hãy nhập label.',
            'value.required' => 'Hãy nhập giá trị.',
        ]);

        $margin = Margin::forceCreate([
            'label' => request('label'),
            'value' => request('value'),
        ]);

        flash()->success('Success!', 'Margin successfully created.');

        return redirect()->route('margins.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function show(Margin $margin)
    {
        return view('margins.edit', compact('margin'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function edit(Margin $margin)
    {
        return view('margins.edit', compact('margin'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function update(Margin $margin)
    {
        $this->validate(request(), [
            'label' => 'required',
            'value' => 'required',
        ], [
            'label.required' => 'Hãy nhập label.',
            'value.required' => 'Hãy nhập giá trị.',
        ]);

        $margin->forceFill([
            'label' => request('label'),
            'value' => request('value'),
        ])->save();

        flash()->success('Success!', 'Margin successfully updated.');

        return redirect()->route('margins.index');
    }

    public function getDatatables()
    {
        return Margin::getDatatables();
    }

}
