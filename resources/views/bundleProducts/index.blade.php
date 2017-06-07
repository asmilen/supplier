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
            <a href="{{ route('bundleProducts.index') }}">Sản phẩm thuộc dàn máy</a>
        </li>
        <li class="active">Danh sách</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Sản phẩm thuộc dàn máy
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Danh sách
            </small>
            <a class="btn btn-primary pull-right" href="{{ route('bundleCategories.create') }}">
                <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                <span class="hidden-xs">Thêm</span>
            </a>

        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <table id="dataTables-bundles" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục của nhóm sản phẩm</th>
                        <th>Nhóm sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Mặc định</th>
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
    var datatable = $("#dataTables-bundles").DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        pageLength: 100,
        ajax: {
            url: '{!! route('bundleProducts.datatables') !!}',
            data: function (d) {
                //
            }
        },
        columns: [
            {data: 'nameProduct', name: 'nameProduct', orderable: false, searchable: false},
            {data: 'nameCategory', name: 'nameCategory', orderable: false, searchable: false},
            {data: 'nameBundle', name: 'nameBundle', orderable: false, searchable: false},
            {data: 'quantity', name: 'quantity', orderable: false, searchable: false},
            {data: 'is_default', name: 'is_default', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });
});
</script>
@endsection
