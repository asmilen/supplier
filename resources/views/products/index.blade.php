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
            <a href="{{ route('products.index') }}">Sản phẩm</a>
        </li>
        <li class="active">Danh sách</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Sản phẩm
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Danh sách
            </small>
            <a class="btn btn-primary pull-right" href="{{ route('products.create') }}">
                <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                <span class="hidden-xs">Thêm</span>
            </a>
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <table id="dataTables-products" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>SKU</th>
                        <th>Danh mục</th>
                        <th>Nhà SX</th>
                        <th>Mã</th>
                        <th>URL</th>
                        <th>Trạng thái</th>
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
    var datatable = $("#dataTables-products").DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: {
            url: '{!! route('products.datatables') !!}',
            data: function (d) {
                //
            }
        },
        columns: [
            {data: 'name', name: 'name'},
            {data: 'sku', name: 'sku'},
            {data: 'category_id', name: 'category_id'},
            {data: 'manufacturer_id', name: 'manufacturer_id'},
            {data: 'code', name: 'code'},
            {data: 'source_url', name: 'source_url'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    @include('scripts.click-datatable-delete-button')
});
</script>
@endsection
