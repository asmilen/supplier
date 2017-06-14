@extends('layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.css">
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
            <li class="active">Tạo mới</li>
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
                    Tạo mới
                </small>
                <a class="btn btn-primary pull-right" href="{{ route('products.index') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </h1>
        </div><!-- /.page-header -->
        <div class="row">
            <div class="col-xs-12" ng-controller="ProductCreateController">
                <div class="alert alert-danger" ng-show="productForm.errors.length > 0">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        <li ng-repeat="error in productForm.errors">@{{ error }}</li>
                    </ul>
                </div>

                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Danh mục</label>
                        <div class="col-sm-6">
                            <select name="category_id" class="categories" ng-model="productForm.category_id" ng-change="refreshData()" placeholder="-- Chọn danh mục --" ng-disabled="disabled" select2>
                                <option value=""></option>
                                <option ng-repeat="category in categories" value="@{{ category.id }}">@{{ category.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Nhà SX</label>
                        <div class="col-sm-6">
                            <select name="manufacturer_id" class="manufactures" ng-model="productForm.manufacturer_id" placeholder="-- Chọn nhà sản xuất --" ng-disabled="disabled" select2>
                                <option value=""></option>
                                <option ng-repeat="manufacturer in manufacturers" value="@{{ manufacturer.id }}">@{{ manufacturer.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Màu sắc</label>
                        <div class="col-sm-6">
                            <select name="color_id" class="form-control" ng-model="productForm.color_id">
                                <option value="">--Chọn Màu sắc--</option>
                                <option ng-repeat="color in colors" value="@{{ color.id }}">@{{ color.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Loại sản phẩm</label>
                        <div class="col-sm-6">
                            <select name="type" class="form-control" ng-model="productForm.type">
                                <option value="simple">Simple</option>
                                <option value="configurable">Configurable</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" ng-show="productForm.type=='simple'">
                        <label class="col-sm-3 control-label no-padding-right">Sản phẩm cha</label>
                        <div class="col-sm-6">
                            <select name="parent_id" class="productConfigurables" ng-model="productForm.parent_id" placeholder="-- Chọn sản phẩm cha --" ng-disabled="disabled" select2>
                                <option value=""></option>
                                <option ng-repeat="product in productConfigurables" value="@{{ product.id }}">@{{ product.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Tên sản phẩm</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="name" placeholder="Tên sản phẩm" ng-model="productForm.name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Mã sản phẩm</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="code" placeholder="Mã sản phẩm" ng-model="productForm.code">
                            <span class="help-block">
                            Dùng để sinh SKU.
                        </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">URL</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="source_url" placeholder="URL" ng-model="productForm.source_url">
                            <span class="help-block">
                            URL nguồn sản phẩm.
                        </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Mô tả</label>
                        <div class="col-sm-6">
                            <textarea class="form-control" name="description" placeholder="Mô tả sản phẩm" rows="5" ng-model="productForm.description"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Kích hoạt</label>
                        <div class="col-sm-6">
                            <label>
                                <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6" ng-model="productForm.status">
                                <span class="lbl"></span>
                            </label>
                        </div>
                    </div>

                    <div ng-if="attributes.length > 0">
                        <hr>

                        <div class="form-group" ng-repeat="attribute in attributes">
                            <label class="col-sm-3 control-label no-padding-right">@{{ attribute.name }}</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="attributes" placeholder="@{{ attribute.name }}" ng-model="productForm.attributes[attribute.slug]">
                            </div>
                        </div>
                    </div>

                    <div ng-show="productForm.type=='configurable'">
                        <label class="control-label no-padding-right">Sản phẩm thuộc combo</label>
                        <br>
                        <div>
                            <table class="table hoverTable" id="products-table">
                                <thead>
                                <th>ID</th>
                                <th>Tên sản phẩm</th>
                                <th>Sku</th>
                                <th>Thao tác</th>
                                </thead>
                                <tbody id = "bundleProducts">

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="clearfix form-actions">
                        <div class="col-md-offset-3 col-md-9">
                            <button type="submit" class="btn btn-success" ng-click="addProduct()" ng-disabled="productForm.disabled">
                                <i class="ace-icon fa fa-save bigger-110"></i>Lưu
                            </button>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModalProduct" ng-show="productForm.type=='configurable'">
                                <i class="ace-icon fa fa-save bigger-110"></i>Chọn sản phẩm con
                            </button>

                            <!-- Modal Product to Connect -->
                            <div class="modal fade" id="myModalProduct" role="dialog">
                                <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Chọn sản phẩm cho nhóm sản phẩm</h4>
                                        </div>
                                        <div class="modal-body">
                                            <table id="tableproducts" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Tên</th>
                                                    <th>SKU</th>
                                                    <th>Trạng thái</th>
                                                    <th>Chọn </th>
                                                    <th>Số Lượng</th>
                                                </tr>
                                                </thead>
                                                <tbody id="productsRegion">

                                                </tbody>
                                            </table>
                                            <br>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label no-padding-left"></label>
                                                <button type="button" class="btn btn-success" id = "btnChooseProduct">
                                                    <i class="ace-icon fa fa-save bigger-110"></i>Chọn sản phẩm
                                                </button>
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- /.page-content -->
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.min.js"></script>
@endsection

@section('inline_scripts')
    <script>
        $(function () {
            $(".categories").select2({
                placeholder: "-- Chọn danh mục --",
                allowClear: true,
                width:'300px',
            });
            $(".manufactures").select2({
                placeholder: "-- Chọn nhà sản xuất --",
                allowClear: true,
                width:'100%',
            });
            $(".productConfigurables").select2({
                placeholder: "-- Chọn sản phẩm cha --",
                allowClear: true,
                width:'100%',
            });

            var productsTable = '';

            productsTable = $("#products-table").DataTable({
                autoWidth: false,
                pageLength: 10,
            });

            var table = $("#tableproducts").DataTable({
                searching: true,
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{!! route('products.getProductInCombo') !!}",
                    data: function (d) {

                    },
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name', className:'productName', "searchable": true},
                    {data: 'sku', name: 'sku', className:'productSku', "searchable": true},
                    {data: 'status', name: 'status'},
                    {data: 'check', name: 'check', orderable: false, searchable: false},
                    {data: 'quantity',name: 'quantity', orderable: false, searchable: false, visible:false},
                ],
            });

            var product_ids = [];

            $(document).on('click', '#btnChooseProduct', function(e) {
                productsTable.destroy();
                var productNames = [];
                var productIds = [];
                var productSkus = [];
                var productQtys = [];
                var rowcollection =  table.$(".checkbox:checked", {"page": "all"});
                for(var i = 0; i < rowcollection.length; i++)
                {
                    productNames.push($(rowcollection[i]).closest('tr').find('.productName').text());
                    productIds.push(parseInt($(rowcollection[i]).val()));
                    product_ids.push(parseInt($(rowcollection[i]).val()));
                    productSkus.push($(rowcollection[i]).closest('tr').find('.productSku').text());
                    productQtys.push($(rowcollection[i]).closest('tr').find('.qty').val());
                }

                for(var i = 0; i < productNames.length; i++) {

                    $("#bundleProducts").append('<tr>' +
                        '<input type ="hidden" name= "productIds[]" value="' +productIds[i] + '"/>' +
                        '<td class="id">' + productIds[i] + '</td>'   +
                        '<td class="name">' + productNames[i] + '</td>' +
                        '<td class="sku">' + productSkus[i] + '</td>'  +
                        '<td><input type = "number" name = "quantity[]" min = 0 value="' + productQtys[i] + '"/></td>'  +
                        '<td><a class="deleteProduct" href=""><i class="fa fa-trash-o" aria-hidden="true"></i></a></td>'  +
                        + '</tr>');
                }
                $("#myModalProduct").hide();
                $("body").removeClass("modal-open");
                productsTable = $("#products-table").DataTable({
                    autoWidth: false
                });

                loadProduct(product_ids);
            });

            $(document).on('click', '.deleteProduct', function(e) {
                e.preventDefault();
                productsTable.row( $(this).parents('tr') ).remove().draw();
                var dataRows = productsTable.rows().data();
                var productIds = [];
                for (var i = 0; i< dataRows.length; i++) {
                    productIds.push(parseInt(dataRows[i][0]));
                }
                loadProduct(productIds);
            });

            function loadProduct(productIds) {
                productIds = typeof productIds !== 'undefined' ? productIds : [];
                table.destroy();
                table = $("#tableproducts").DataTable({
                    searching: true,
                    autoWidth: false,
                    processing: true,
                    serverSide: true,
                    pageLength: 10,
                    ajax: {
                        url: "{!! route('products.getProductInCombo') !!}",
                        data: function (d) {
                            d.productIds = productIds
                        },
                    },
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'name', name: 'name', searchable: true, className:'productName'},
                        {data: 'sku', name: 'sku', searchable: true, className:'productSku'},
                        {data: 'status', name: 'status'},
                        {data: 'check', name: 'check', orderable: false, searchable: false},
                        {data: 'quantity',name: 'quantity', orderable: false, searchable: false, visible:false},
                    ],
                });
            }

        });
    </script>
@endsection
