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
            <a href="{{ route('users.index') }}">Users</a>
        </li>
        <li class="active">List</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Users
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                List
            </small>
            <a class="btn btn-primary pull-right" href="{{ route('users.create') }}">
                <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                <span class="hidden-xs">Add</span>
            </a>
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <div class="widget-box">
                <div class="widget-header">
                    <h5 class="widget-title">Search</h5>
                </div>

                <div class="widget-body">
                    <div class="widget-main">
                        <form class="form-inline" id="search-form">
                            <input type="text" class="form-control" placeholder="Name" name="name" />
                            <input type="text" class="form-control" placeholder="Email" name="email" />
                            <button type="submit" class="btn btn-purple btn-sm">
                                <span class="ace-icon fa fa-search icon-on-right bigger-110"></span> Search
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <table id="dataTables-users" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role(s)</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection

@section('scripts')
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
@endsection

@section('inline_scripts')
<script>
$(function () {
    var datatable = $("#dataTables-users").DataTable({
        searching: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! route('users.datatables') !!}',
            data: function (d) {
                d.name = $('input[name=name]').val();
                d.email = $('input[name=email]').val();
            }
        },
        columns: [
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'roles', name: 'roles', orderable: false, searchable: false},
            {data: 'last_login', name: 'last_login'},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#search-form').on('submit', function(e) {
        datatable.draw();
        e.preventDefault();
    });

    @include('scripts.click-datatable-delete-button')
});
</script>
@endsection
