<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = (new Category)->forceFill([
            'status' => true,
        ]);

        return view('categories.create', compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'name' => 'required|max:255|unique:categories',
            'code' => 'required|alpha_num|min:3|max:3|unique:categories',
            'margin' => 'integer|between:0,100',
        ], [
            'name.unique' => 'Tên danh mục đã tồn tại.',
            'code.unique' => 'Mã danh mục đã tồn tại.',
            'margin.between' => 'Margin phải lớn hơn bằng 0 và nhỏ hơn bằng 100',
        ]);

        $category = Category::forceCreate([
            'name' => request('name'),
            'code' => strtoupper(request('code')),
            'status' => !! request('status'),
            'margin' => request('margin'),
        ]);

        flash()->success('Success!', 'Category successfully created.');

        return redirect()->route('categories.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Category $category)
    {
        $this->validate(request(), [
            'name' => 'required|max:255|unique:categories,name,'.$category->id,
            'margin' => 'integer|between:0,100',
        ], [
            'name.unique' => 'Tên danh mục đã tồn tại.',
            'margin.between' => 'Margin phải lớn hơn bằng 0 và nhỏ hơn bằng 100',
        ]);

        $category->forceFill([
            'name' => request('name'),
            'status' => !! request('status'),
            'margin' => request('margin'),
        ])->save();

        flash()->success('Success!', 'Category successfully updated.');

        return redirect()->route('categories.index');
    }

    public function getDatatables()
    {
        return Category::getDatatables();
    }
}
