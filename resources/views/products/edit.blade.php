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
        <li class="active">Thay đổi</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content" ng-controller="ProductEditController">
    <div class="page-header">
        <h1>
            Sản phẩm
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Thay đổi
            </small>
            <a class="btn btn-primary pull-right" href="{{ route('products.index') }}">
                <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                <span class="hidden-xs">Danh sách</span>
            </a>
        </h1>
    </div><!-- /.page-header -->
    <div class="row" ng-if="productIsLoaded">
        <div class="col-xs-12">
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
                        <select name="category_id" class="form-control" ng-model="productForm.category_id" ng-change="refreshData()">
                            <option value="">--Chọn Danh mục--</option>
                            <option ng-repeat="category in categories" value="@{{ category.id }}">@{{ category.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Nhà SX</label>
                    <div class="col-sm-6">
                        <select name="manufacturer_id" class="form-control" ng-model="productForm.manufacturer_id">
                            <option value="">--Chọn Nhà SX--</option>
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

                @if($product->type == 0)
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Sản phẩm cha</label>
                    <div class="col-sm-6">
                        <select name="parent_id" class="form-control" ng-model="productForm.parent_id">
                            <option value="">--Chọn Sản Phẩm Cha--</option>
                            <option ng-repeat="product in productConfigurables" value="@{{ product.id }}">@{{ product.name }}</option>
                        </select>
                    </div>
                </div>
                @endif

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

                <div class="form-group" ng-if="product.old_sku">
                    <label class="col-sm-3 control-label no-padding-right">Mã SKU cũ trên kho</label>
                    <div class="col-sm-6">
                        <p class="form-control-static"><strong>@{{ ::product.old_sku }}</strong></p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Mã SKU</label>
                    <div class="col-sm-6">
                        <p class="form-control-static"><strong>@{{ ::product.sku }}</strong></p>
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

                <div class="clearfix form-actions">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="submit" class="btn btn-success" ng-click="updateProduct()" ng-disabled="productForm.disabled">
                            <i class="ace-icon fa fa-save bigger-110"></i>Lưu
                        </button>
                        @if($product->type == 1)
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModalProduct">
                                <i class="ace-icon fa fa-save bigger-110"></i>Chọn sản phẩm con
                            </button>
                        @endif
                            <!-- Modal Product to Connect -->

                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Product to Connect -->
    <div class="modal fade" id="myModalProduct" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Chọn sản phẩm con</h4>
                </div>
                <div class="modal-body">
                    <table id="product-childs" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>SKU</th>
                            <th>Trạng thái</th>
                            <th>Thao tác </th>
                        </tr>
                        </thead>
                    </table>
                    <br>
                    <div class="form-group">
                        <label class="col-sm-4 control-label no-padding-left"></label>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                           Xong
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if(count($productChilds))
    <div class="row">
        <h3>Sản phẩm con </h3>
        <div class="col-xs-12">
            <table id="products-table" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Sản phẩm</th>
                    <th>SKU</th>
                    <th>Thao tác</th>
                </tr>
                </thead>
                <tbody id="productChildren">
                @foreach($productChilds as $key => $value)
                    <tr>
                        <td>{{ $value->id }}</td>
                        <td>{{ $value->name }}</td>
                        <td>{{ $value->sku }}</td>
                        <td><button ng-click="removeChild({{$value->id}})"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div><!-- /.page-content -->
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
@endsection

@section('inline_scripts')
<script type="text/javascript">
    var PRODUCT_ID = '{{ $product->id }}';
    $(function ()  {

        $('#myModalProduct').on('hidden.bs.modal', function () {
           window.location.reload();
        })

        var table = $("#product-childs").DataTable({
            searching: true,
            autoWidth: false,
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: "{!! route('products.getSimpleProduct') !!}",
                data: function (d) {
                },
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name', className:'productName', "searchable": true},
                {data: 'sku', name: 'sku', className:'productSku', "searchable": true},
                {data: 'status', name: 'status'},
                {data: 'add', name: 'add', orderable: false, searchable: false, className:'addChild'},
            ],
        });

        $(document).on('click', '.addChild', function(e) {
            var productChild = table.row( this ).data().id ;
            $.ajax({
                url: "/products/" + PRODUCT_ID + "/addChild",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    productChild: productChild,
                } ,
                success: function (response) {
                    if (response.status == 'success') {
                        swal({
                            title: "Thành công!",
                            text: "Thêm sản phẩm con thành công",
                            type: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });
});

</script>
@endsection
