@extends('layouts.app')

@section('content')
<!-- #section:basics/content.breadcrumbs -->
<div class="breadcrumbs" id="breadcrumbs">
    <script type="text/javascript">
        try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
    </script>

    <ul class="breadcrumb">
        <li>
            <i class="ace-icon fa fa-home home-icon"></i>
            <a href="{{ url('/dashboard') }}">Dashboard</a>
        </li>
        <li>
            <a href="{{ route('roles.index') }}">Roles</a>
        </li>
        <li>
            <a href="{{ route('roles.show', $role->id) }}">{{ $role->name }}</a>
        </li>
        <li class="active">Permissions</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Role Permissions
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                {{ $role->name }}
            </small>
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <form role="form" method="POST" action="{{ route('rolePermissions.update', $role->id) }}">
                {!! csrf_field() !!}
                {!! method_field('PUT') !!}
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Has Access</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission['name'] }}</td>
                            <td>
                                <label>
                                    <input class="ace ace-switch ace-switch-6"{{ $role->hasAccess($permission['name']) ? ' checked="checked"' : '' }} name="permissions[{{ $permission['name'] }}]" type="checkbox" value="1">
                                    <span class="lbl"></span>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="clearfix form-actions">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="submit" class="btn btn-success">
                            <i class="ace-icon fa fa-save bigger-110"></i>Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div><!-- /.row -->
</div><!-- /.page-content -->
@endsection