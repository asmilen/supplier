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
            <a href="{{ url('/model-tracking-logs') }}">Model Tracking Log</a>
        </li>
        <li class="active">Danh sách</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Model Tracking Log
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Danh sách
            </small>
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
                            <select class="form-control" name="model_type">
                                <option value="">-- Chọn Model --</option>
                                @foreach ($modelTypes as $modelType)
                                <option value="{{ $modelType }}">{{ $modelType }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" placeholder="Model ID" name="model_id" />
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
            <table id="dataTables-model-tracking-logs" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th>Model ID</th>
                        <th>Action</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Updater</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
@endsection

@section('inline_scripts')
<script>
$(function () {
    var datatable = $("#dataTables-model-tracking-logs").DataTable({
        searching: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! url('/model-tracking-logs/datatables') !!}',
            data: function (d) {
                d.model_type = $('select[name=model_type]').val();
                d.model_id = $('input[name=model_id]').val();
            }
        },
        columns: [
            {data: 'trackable_type', name: 'trackable_type'},
            {data: 'trackable_id', name: 'trackable_id'},
            {data: 'action', name: 'action'},
            {data: 'before', name: 'before'},
            {data: 'after', name: 'after'},
            {data: 'user_id', name: 'user_id'},
            {data: 'created_at', name: 'created_at'},
        ],
        pageLength: 100,
        order: [6, 'desc']
    });

    $('#search-form').on('submit', function(e) {
        datatable.draw();
        e.preventDefault();
    });
});
</script>
@endsection
