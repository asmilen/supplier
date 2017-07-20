<?php

namespace App\Http\Controllers;

use Sentinel;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update()
    {
        $this->validate(request(), [
            'name' => 'required|unique:name|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.Sentinel::getUser()->id,
        ], [
            'name.required' => "Vui lòng nhập Name.",
            'email.required' => "Vui lòng nhập Email.",
            'name.max:255' => "Tên của bạn quá dài, tối đa 255 kí tự.",
            'name.unique' => "Tên của bạn đã tồn tại.",
        ]);

        Sentinel::update(Sentinel::getUser(), request()->all());

        flash()->success('Success!', 'Profile successfully updated.');

        return redirect()->back();
    }

    public function editPassword()
    {
        return view('profile.password.edit');
    }

    public function updatePassword()
    {
        $this->validate(request(), [
            'current_password' => 'required|passcheck',
            'password' => 'required|confirmed|min:6',
        ], [
            'current_password.required' => "Vui lòng nhập Current Password.",
            'password.required' => "Vui lòng nhập New Password.",
        ]);

        Sentinel::update(Sentinel::getUser(), request()->only('password'));

        flash()->success('Success!', 'Password successfully updated.');

        return redirect()->back();
    }
}
