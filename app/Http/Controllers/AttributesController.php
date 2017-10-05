<?php

namespace App\Http\Controllers;

use App\Models\Attribute;

class AttributesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('attributes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $attribute = new Attribute;

        return view('attributes.create', compact('attribute'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'name' => 'required|max:255|unique:attributes',
        ], [
            'name.required' => 'Tên thuộc tính là bắt buộc',
            'name.max' => 'Tên thuộc tính ít hơn 255 kí tự',
            'name.unique' => 'Tên thuộc tính đã tồn tại.',
        ]);

        $attribute = Attribute::forceCreate([
            'name' => request('name'),
        ]);

        flash()->success('Success!', 'Attribute successfully created.');

        return redirect()->route('attributes.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function show(Attribute $attribute)
    {
        return view('attributes.edit', compact('attribute'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function edit(Attribute $attribute)
    {
        return view('attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */

    public function update(Attribute $attribute)
    {
        $this->validate(request(), [
            'name' => 'required|max:255|unique:attributes,name,'.$attribute->id,
        ], [
            'name.required' => 'Tên thuộc tính là bắt buộc',
            'name.max' => 'Tên thuộc tính ít hơn 255 kí tự',
            'name.unique' => 'Tên thuộc tính đã tồn tại.',
        ]);

        $attribute->forceFill([
            'name' => request('name'),
        ])->save();

        flash()->success('Success!', 'Attribute successfully updated.');

        return redirect()->route('attributes.index');
    }

    public function listing()
    {
        $sorting = request('sorting', 'slug');

        $direction = request('direction', 'asc');

        $page = request('page', 1);

        $limit = request('limit', 25);

        $builder = Attribute::where(function ($query) {
            if (! empty(request('q'))) {
                $query->where('id', 'like', '%'.request('q').'%')
                    ->orWhere('slug', 'like', '%'.request('q').'%')
                    ->orWhere('name', 'like', '%'.request('q').'%')
                    ->orWhere('backend_type', 'like', '%'.request('q').'%')
                    ->orWhere('frontend_input', 'like', '%'.request('q').'%');
            }
        });

        $totalItems = $builder->count();

        $attributes = $builder
            ->orderBy($sorting, $direction)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => $attributes,
            'total_items' => $totalItems,
        ]);
    }
}
