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
        $category = new Category;

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
            'code' => 'alpha_num|max:3|unique:categories',
        ], [
            'name.unique' => 'Tên danh mục đã tồn tại',
            'code.unique' => 'Mã danh mục đã tồn tại',
        ]);

        $category = Category::forceCreate([
            'name' => request('name'),
            'code' => strtoupper(request('code')),
            'status' => !! request('status'),
        ]);

        if (empty($category->code)) {
            $category->forceFill([
                'code' => $category->id,
            ])->save();
        }

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
        ], [
            'name.unique' => 'Tên danh mục đã tồn tại',
        ]);

        $category->forceFill([
            'name' => request('name'),
            'status' => !! request('status'),
        ])->save();

        flash()->success('Success!', 'Category successfully updated.');

        return redirect()->route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();

        flash()->success('Success!', 'Category successfully deleted.');
    }

    public function getDatatables()
    {
        return Category::getDatatables();
    }
}
