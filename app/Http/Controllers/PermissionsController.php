<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionsController extends Controller
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

    public function index()
    {
        $permissions = $this->permissionModel->all();

        return view('permissions.index', compact('permissions'));
    }
}
