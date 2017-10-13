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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'slug' => 'required|max:30|regex:/(^[a-z0-9_]+$)+/|unique:attributes',
            'name' => 'required|max:255',
        ], [
            'slug.required' => 'Mã thuộc tính là bắt buộc',
            'slug.max' => 'Mã thuộc tính ít hơn 30 kí tự',
            'slug.unique' => 'Mã thuộc tính đã tồn tại.',
            'slug.regex' => 'Vui lòng chỉ sử dụng chữ cái (a-z), số (0-9) hoặc gạch dưới (_) cho Mã thuộc tính.',
            'name.required' => 'Tên hiển thị là bắt buộc',
            'name.max' => 'Tên hiển thị ít hơn 255 kí tự',
        ]);

        $attribute = Attribute::forceCreate([
            'slug' => request('slug'),
            'name' => trim(request('name')),
            'frontend_input' => request('frontend_input'),
            'backend_type' => $this->mapBackendType(),
        ]);

        return $attribute;
    }

    protected function mapBackendType()
    {
        $frontendInput = request('frontend_input');

        if ($frontendInput == 'textarea') {
            return 'text';
        }

        if ($frontendInput == 'select' || $frontendInput == 'multiselect') {
            return 'int';
        }

        return request('backend_type', 'varchar');
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
            'name' => trim(request('name')),
        ])->save();

        return $attribute;
    }

    public function listing()
    {
        $sorting = request('sorting', 'slug');

        $direction = request('direction', 'asc');

        $page = request('page', 1);

        $limit = request('limit', 25);

        $builder = Attribute::where(function ($query) {
            if (! empty(request('q'))) {
                $query->where(function ($q) {
                    $q->where('id', 'like', '%'.request('q').'%')
                        ->orWhere('slug', 'like', '%'.request('q').'%')
                        ->orWhere('name', 'like', '%'.request('q').'%')
                        ->orWhere('backend_type', 'like', '%'.request('q').'%')
                        ->orWhere('frontend_input', 'like', '%'.request('q').'%');
                });
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
