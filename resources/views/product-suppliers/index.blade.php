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
            <a href="{{ url('/product-suppliers') }}">Sản phẩm - Nhà cung cấp</a>
        </li>
        <li class="active">Danh sách</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content" ng-controller="ProductSupplierIndexController">
    <div class="page-header">
        <h1>
            Sản phẩm - Nhà cung cấp
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Danh sách
            </small>
            <a class="btn btn-primary pull-right" ng-click="showAddProductSupplierModal()">
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
                            <select class="form-control" name="category_id">
                                <option value="">-- Danh mục --</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <select class="form-control" name="manufacturer_id">
                                <option value="">-- Nhà sản xuất --</option>
                                @foreach ($manufacturers as $manufacturer)
                                <option value="{{ $manufacturer->id }}">{{ $manufacturer->name }}</option>
                                @endforeach
                            </select>
                            <select class="form-control" name="supplier_id">
                                <option value="">-- Nhà cung cấp --</option>
                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" placeholder="Tên hoặc SKU sản phẩm" name="keyword" />
                            <select class="form-control" name="state">
                                <option value="">-- Trạng thái hàng --</option>
                                @foreach (config('teko.product.state') as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
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
            <table id="dataTables-product-suppliers" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>Danh mục</th>
                        <th>Nhà sản xuất</th>
                        <th>SKU</th>
                        <th>Tên</th>
                        <th>NCC</th>
                        <th>Giá nhập</th>
                        <th>Hiệu lực từ</th>
                        <th>Hiệu lực đến</th>
                        <th>Số lượng tối thiểu</th>
                        <th>Giá bán khuyến nghị</th>
                        <th>Tình trạng</th>
                        <th>Người cập nhật</th>
                        <th>Cập nhật lần cuối</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modal-add-product-supplier" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Thêm giá nhập Sản phẩm theo Nhà cung cấp</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" ng-show="addProductSupplierForm.errors.length > 0">
                        <ul>
                            <li ng-repeat="error in addProductSupplierForm.errors">@{{ error }}</li>
                        </ul>
                    </div>
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Sản phẩm</label>
                            <div class="col-sm-6">
                                <p class="form-control-static">
                                    <a class="action-link" ng-click="showSelectProductModal()">@{{ addProductSupplierForm.product_id ? addProductSupplierForm.product_name : 'Chọn sản phẩm' }}</a>
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Nhà cung cấp</label>
                            <div class="col-sm-6">
                                <p class="form-control-static">
                                    <a class="action-link" ng-click="showSelectSupplierModal()">@{{ addProductSupplierForm.supplier_id ? addProductSupplierForm.supplier_name : 'Chọn nhà cung cấp' }}</a>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" ng-click="addProductSupplier()" ng-disabled="addProductSupplierForm.disabled"><i class="fa fa-save"></i> Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-select-product" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Chọn sản phẩm</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-select-supplier" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Chọn nhà cung cấp</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                </div>
            </div>
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
    var datatable = $("#dataTables-product-suppliers").DataTable({
        searching: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! url('/product-suppliers/datatables') !!}',
            data: function (d) {
                d.category_id = $('select[name=category_id]').val();
                d.manufacturer_id = $('select[name=manufacturer_id]').val();
                d.supplier_id = $('select[name=supplier_id]').val();
                d.keyword = $('input[name=keyword]').val();
                d.state = $('select[name=state]').val();
            }
        },
        columns: [
            // {data: 'action', name: 'action', orderable: false, searchable: false},
            {data: 'category_name', name: 'category_name', orderable: false},
            {data: 'manufacturer_name', name: 'manufacturer_name', orderable: false},
            {data: 'sku', name: 'sku', orderable: false},
            {data: 'product_name', name: 'product_name', orderable: false},
            {data: 'supplier_name', name: 'supplier_name', orderable: false},
            {data: 'import_price', name: 'import_price'},
            {data: 'from_date', name: 'from_date'},
            {data: 'to_date', name: 'to_date'},
            {data: 'min_quantity', name: 'min_quantity'},
            {data: 'price_recommend', name: 'price_recommend'},
            {data: 'state', name: 'state'},
            {data: 'updated_by', name: 'updated_by'},
            {data: 'updated_at', name: 'updated_at'},
        ],
        columnDefs: [
            {className: 'text-right', 'targets': [5,6,7,8,9]}
        ],
        pageLength: 50,
        order: [12, 'desc']
    });

    $('#search-form').on('submit', function(e) {
        datatable.draw();
        e.preventDefault();
    });
});
</script>
@endsection
