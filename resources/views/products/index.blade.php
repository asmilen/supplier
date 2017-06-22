@extends('layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.css">
    <style>
        .select2-container, .select2-drop, .select2-search, .select2-container .select2-search input{vertical-align: middle;}
        .select2-search:after {
            font-family: FontAwesome;
            font-size: 14px;
            display: inline;
            content: "" !important;
            color: #777;
            position: relative;
            top: 0;
            left: -20px;
            z-index: 0;
        }
    </style>

@endsection
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
            <div class="widget-box">
                <div class="widget-header">
                    <h5 class="widget-title">Search</h5>
                </div>

                <div class="widget-body">
                    <div class="widget-main">
                        <form class="form-inline" id="search-form">
                            <select class="categories" name="category_id">
                                <option value=""></option>
                                @foreach ($categoriesList as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <select class="manufactures" name="manufacturer_id">
                                <option value=""></option>
                                @foreach ($manufacturersList as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <select class="form-control" name="status">
                                <option value="">--Chọn Trạng thái--</option>
                                <option value="active">Kích hoạt</option>
                                <option value="inactive">Không kích hoạt</option>
                            </select>
                            <input type="text" class="form-control" name="keyword" placeholder="Từ khóa tìm kiếm" />
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
            <table id="dataTables-products" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Tên</th>
                        <th>SKU</th>
                        <th>Danh mục</th>
                        <th>Nhà SX</th>
                        <th>Mã</th>
                        <th>Link Ảnh</th>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.min.js"></script>
@endsection

@section('inline_scripts')
<script>
$(function () {
    $(".categories").select2({
        placeholder: "-- Chọn danh mục --",
        allowClear: true,
        width:'10%',
    });
    $(".manufactures").select2({
        placeholder: "-- Chọn nhà sản xuất --",
        allowClear: true,
        width:'11%',
    });

    var datatable = $("#dataTables-products").DataTable({
        searching: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: {
            url: '{!! route('products.datatables') !!}',
            data: function (d) {
                d.category_id = $('select[name=category_id]').val();
                d.manufacturer_id = $('select[name=manufacturer_id]').val();
                d.keyword = $('input[name=keyword]').val();
                d.status = $('select[name=status]').val();
            }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'sku', name: 'sku'},
            {data: 'category_id', name: 'category_id'},
            {data: 'manufacturer_id', name: 'manufacturer_id'},
            {data: 'code', name: 'code'},
            {data: 'image', name: 'image'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#search-form').on('submit', function(e) {
        datatable.draw();
        e.preventDefault();
    });

    datatable.on('click', '[id^="btn-toggle-"]', function (e) {
        e.preventDefault();

        var url = $(this).data('url');

        $.ajax({
            url: url,
            type: "POST",
            beforeSend: function (xhr) {
                return xhr.setRequestHeader('X-CSRF-TOKEN', window.Laradmin.csrfToken);
            },
            success: function (districts) {
                var row = $(this).closest('tr');
                datatable.row(row).draw();
            },
            error: function () {
            }
        });
    });

});
</script>
@endsection

