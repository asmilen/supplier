<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Attribute;
use App\Jobs\PublishMessage;

class CategoriesController extends Controller
{
    public function __construct()
    {
        view()->share('attributesList', Attribute::getList());
    }

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
        ], [
            'name.max' => 'Tên danh mục quá dài, tối đa 255 ký tự.',
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'code.required' => 'Vui lòng nhập mã danh mục.',
            'name.unique' => 'Tên danh mục đã tồn tại.',
            'code.unique' => 'Mã danh mục đã tồn tại.',
            'code.alpha_num' => 'Mã danh mục không được chứa kí tự đặc biệt.',
            'code.min' => 'Mã danh mục dài 3 kí tự.',
            'code.max' => 'Mã danh mục dài 3 kí tự.',
        ]);

        $category = Category::forceCreate([
            'name' => trim(request('name')),
            'code' => strtoupper(request('code')),
            'status' => !! request('status'),
        ]);

        $category->attributes()->attach(request('attributes', []));

        $jsonSend = [
            "id"        => $category->id,
            "code"      => strtoupper(request('code')),
            "name"      => request('name'),
            "status"    => $category->status == true ? 'active' : 'inactive',
            "createdAt" => strtotime($category->created_at)
        ];
        $messSend = json_encode($jsonSend);

        dispatch(new PublishMessage('teko.sale', 'sale.cat.upsert', $messSend));

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
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục quá dài, tối đa 255 kí tự.',
            'name.unique' => 'Tên danh mục đã tồn tại.',
        ]);

        $category->forceFill([
            'name' => trim(request('name')),
            'status' => !! request('status'),
        ])->save();

        $category->attributes()->sync(request('attributes', []));

        $jsonSend = [
            "id"        => $category->id,
            "code"      => $category->code,
            "name"      => request('name'),
            "status"    => $category->status == true ? 'active' : 'inactive',
            "createdAt" => strtotime($category->updated_at)
        ];
        $messSend = json_encode($jsonSend);

        dispatch(new PublishMessage('teko.sale', 'sale.cat.upsert', $messSend));

        flash()->success('Success!', 'Category successfully updated.');

        return redirect()->route('categories.index');
    }

    public function getDatatables()
    {
        return Category::getDatatables();
    }

    public function all()
    {
        return Category::orderBy('name', 'asc')->get();
    }
}
