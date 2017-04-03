<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;

class UserPermissionsController extends Controller
{
    protected $permissionModel;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Permission $permissionModel)
    {
        $this->permissionModel = $permissionModel;
    }

    public function index(User $user)
    {
        $permissions = $this->permissionModel->all();

        return view('users.permissions.index', compact('user', 'permissions'));
    }

    public function update(User $user)
    {
        $user->grantPermissions(request('permissions', []));

        flash()->success('Success!', 'User Permissions successfully updated.');

        return redirect()->route('userPermissions.index', $user->id);
    }
}
