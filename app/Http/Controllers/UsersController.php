<?php

namespace App\Http\Controllers;

use Sentinel;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        view()->share('rolesList', Role::all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = new User;

        return view('users.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'level' => 'required',
            'areas' => 'required|array',
            'areas.*' => 'required',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'name.max' => 'Tên quá dài, tối đa 255 kí tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.max' => 'Email quá dài, tối đa 255 kí tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 kí tự.',
        ]);

        $user = Sentinel::register(
            request()->all(), !! request('active')
        )->syncRoles(request('roles', []));

        $user->setUserSupportedProvince(request('level'),request('areas'));

        flash()->success('Success!', 'User successfully created.');

        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate(request(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'min:6|confirmed',
            'level' => 'required',
            'areas' => 'required|array',
            'areas.*' => 'required',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'name.max' => 'Tên quá dài, tối đa 255 kí tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.max' => 'Email quá dài, tối đa 255 kí tự.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 kí tự.',
        ]);

        $user->update(request()->all())
            ->setActivation(!! request('active'))
            ->syncRoles(request('roles', []));

        $user->setUserSupportedProvince(request('level'),request('areas'));

        flash()->success('Success!', 'User successfully updated.');

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        flash()->success('Success!', 'User successfully deleted.');
    }

    public function getDatatables()
    {
        return User::getDatatables();
    }
}
