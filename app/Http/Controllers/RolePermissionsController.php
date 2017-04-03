<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RolePermissionsController extends Controller
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

    public function index(Role $role)
    {
        $permissions = $this->permissionModel->all();

        return view('roles.permissions.index', compact('role', 'permissions'));
    }

    public function update(Role $role)
    {
        $role->grantPermissions(request('permissions', []));

        flash()->success('Success!', 'Role Permissions successfully updated.');

        return redirect()->route('rolePermissions.index', $role->id);
    }
}
