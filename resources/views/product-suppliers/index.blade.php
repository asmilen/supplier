@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="/vendor/ace/assets/css/datepicker.css" />
@endsection

@section('content')
        <!-- #section:basics/content.breadcrumbs -->
<div class="breadcrumbs" id="breadcrumbs">
    <script type="text/javascript">
        try {
            ace.settings.check('breadcrumbs', 'fixed')
        } catch (e) {
        }
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
                            <input type="text" class="form-control" placeholder="Tên hoặc SKU sản phẩm" name="keyword"/>
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
{{--                        <form class="form-inline" action="{{ url('product-suppliers/update-price') }}" method="get" style="margin-top: 10px">--}}
                        <button type="submit" class="btn btn-success btn-sm" id="btn_show"  style="margin-top: 10px">
                            Update Price to Magento
                        </button>
                        {{--</form>--}}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="myModalRunJob" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header" style="text-align: center">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Cập nhật giá sang Magento sẽ mất thời gian chạy ngầm.<br> Bạn có đồng ý cập nhật giá không?</h4>
                    </div>
                    <div  style="text-align: center; margin-top: 10px" >
                        <button class="btn btn-success btn-sm" id="btn_price" style="margin-right: 30px" data-dismiss="modal">Đồng ý</button>
                        <button class="btn btn-danger btn-sm" id="btn_price"  data-dismiss="modal">Hủy</button>
                    </div>
                    <div class="modal-body" id="CheckStatusBody">
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
                    <div class="alert alert-success" ng-if="addProductSupplierForm.success">
                        Thêm giá nhập theo nhà cung cấp thành công.
                    </div>
                    <div class="alert alert-danger" ng-show="addProductSupplierForm.errors.length > 0">
                        <ul>
                            <li ng-repeat="error in addProductSupplierForm.errors">@{{ error }}</li>
                        </ul>
                    </div>
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Sản phẩm</label>
                            <div class="col-sm-9">
                                <p class="form-control-static">
                                    <a class="action-link" ng-click="showSelectProductModal()">@{{ addProductSupplierForm.product_id ? addProductSupplierForm.product_name : 'Chọn sản phẩm' }}</a>
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Nhà cung cấp</label>
                            <div class="col-sm-9">
                                <p class="form-control-static">
                                    <a class="action-link" ng-click="showSelectSupplierModal()">@{{ addProductSupplierForm.supplier_id ? addProductSupplierForm.supplier_name : 'Chọn nhà cung cấp' }}</a>
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Giá nhập (có VAT)</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" ng-model="addProductSupplierForm.import_price" placeholder="Giá nhập (có VAT)">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Hiệu lực giá</label>
                            <div class="col-sm-9">
                                <div class="input-daterange input-group">
                                    <input type="text" class="input-sm form-control" ng-model="addProductSupplierForm.from_date" placeholder="Từ" />
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="input-sm form-control" ng-model="addProductSupplierForm.to_date" placeholder="Đến" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Số lượng tối thiểu</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" ng-model="addProductSupplierForm.min_quantity" placeholder="Số lượng tối thiểu">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Giá bán khuyến nghị</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" ng-model="addProductSupplierForm.price_recommend" placeholder="Giá bán khuyến nghị">
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
                    <div class="row">
                        <div class="col-xs-12">
                            <form class="form-inline">
                                <input type="text" class="input-large" placeholder="ID hoặc SKU" ng-model="productsListForm.q" ng-change="getProductsList(productsListForm.page)">
                                <button type="button" class="btn btn-info btn-sm" ng-click="getProductsList(productsListForm.page)">Tìm kiếm</button>
                            </form>

                            <h3 class="header smaller lighter blue"></h3>

                            <table class="table table-bordered table-hover" ng-show="productsList.length > 0">
                                <thead>
                                    <tr>
                                        <th>Chọn</th>
                                        <th>ID</th>
                                        <th>SKU</th>
                                        <th>Tên</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="product in productsList">
                                        <td>
                                            <button class="btn btn-xs btn-success" ng-click="selectProduct(product)">
                                                <i class="ace-icon fa fa-check bigger-120"></i>
                                            </button>
                                        </td>
                                        <td>@{{ product.id }}</td>
                                        <td>@{{ product.sku }}</td>
                                        <td>@{{ product.name }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <ul uib-pagination boundary-links="true" total-items="productsListForm.total_items" items-per-page="productsListForm.limit" ng-model="productsListForm.page" ng-change="getProductsList(productsListForm.page)" class="pagination" previous-text="&lsaquo;" next-text="&rsaquo;" first-text="&laquo;" last-text="&raquo;" ng-show="productsListForm.total_items > productsListForm.limit"></ul>
                        </div>
                    </div>
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
                    <div class="row">
                        <div class="col-xs-12">
                            <form class="form-inline">
                                <input type="text" class="input-large" placeholder="ID hoặc Tên" ng-model="suppliersListForm.q" ng-change="getSuppliersList(suppliersListForm.page)">
                                <button type="button" class="btn btn-info btn-sm" ng-click="getSuppliersList(suppliersListForm.page)">Tìm kiếm</button>
                            </form>

                            <h3 class="header smaller lighter blue"></h3>

                            <table class="table table-bordered table-hover" ng-show="suppliersList.length > 0">
                                <thead>
                                    <tr>
                                        <th>Chọn</th>
                                        <th>ID</th>
                                        <th>Tên</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="supplier in suppliersList">
                                        <td>
                                            <button class="btn btn-xs btn-success" ng-click="selectSupplier(supplier)">
                                                <i class="ace-icon fa fa-check bigger-120"></i>
                                            </button>
                                        </td>
                                        <td>@{{ supplier.id }}</td>
                                        <td>@{{ supplier.name }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <ul uib-pagination boundary-links="true" total-items="suppliersListForm.total_items" items-per-page="suppliersListForm.limit" ng-model="suppliersListForm.page" ng-change="getSuppliersList(suppliersListForm.page)" class="pagination" previous-text="&lsaquo;" next-text="&rsaquo;" first-text="&laquo;" last-text="&raquo;" ng-show="suppliersListForm.total_items > suppliersListForm.limit"></ul>
                        </div>
                    </div>
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
    <script src="/vendor/ace/assets/js/date-time/bootstrap-datepicker.js"></script>
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

$(document).ready(function () {
    $(document).on("click", "#btn_show", function () {
        $('#myModalRunJob').modal('show');
    });

    $(document).on("click", "#btn_price", function () {
        $.ajax({
            headers: {'X-CSRF-Token': $('input[name="_token"]').val()},
            url: '{{ url('product-suppliers/update-price') }}',
            type: 'GET',
            success: function (res) {
                if (res.status == 'success') {

                }
            }
        });
    });
});

</script>
@endsection
