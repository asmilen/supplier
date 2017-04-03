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
        <li class="active">List</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Roles
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                List
            </small>
            <a class="btn btn-primary pull-right" href="{{ route('roles.create') }}">
                <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                <span class="hidden-xs">Add</span>
            </a>
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <table id="dataTables-roles" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
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
    var datatable = $("#dataTables-roles").DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! route('roles.datatables') !!}',
            data: function (d) {
            }
        },
        columns: [
            {data: 'name', name: 'name'},
            {data: 'slug', name: 'slug'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    @include('scripts.click-datatable-delete-button')
});
</script>
@endsection