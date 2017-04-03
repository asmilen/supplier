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
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.Sentinel::getUser()->id,
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
        ]);

        Sentinel::update(Sentinel::getUser(), request()->only('password'));

        flash()->success('Success!', 'Password successfully updated.');

        return redirect()->back();
    }
}
