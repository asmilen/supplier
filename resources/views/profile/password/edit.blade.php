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
        <li class="active">Update Password</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Update Password
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            @include('common.errors')

            <form class="form-horizontal" role="form" method="POST" action="{{ url('/profile/password') }}">
                {!! csrf_field() !!}
                {!! method_field('PUT') !!}

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Current Password</label>
                    <div class="col-sm-6">
                        <input type="password" class="form-control" name="current_password" placeholder="Current Password">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">New Password</label>
                    <div class="col-sm-6">
                        <input type="password" class="form-control" name="password" placeholder="New Password">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Confirm New Password</label>
                    <div class="col-sm-6">
                        <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm New Password">
                    </div>
                </div>

                <div class="clearfix form-actions">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="submit" class="btn btn-success">
                            <i class="ace-icon fa fa-save bigger-110"></i>Save
                        </button>
                    </div>
                </div>
            </form>
        </div><!-- /.col -->
    </div><!-- /.row -->        
</div><!-- /.page-content -->
@endsection