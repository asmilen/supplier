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
                <a href="{{ route('productAttributes.index') }}">Sản phẩm có thuộc tính</a>
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
                <a class="btn btn-primary pull-right" href="{{ route('productAttributes.index') }}">
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

                <form class="form-horizontal"  role="form" method="POST" action="{{ route('productAttributes.store') }}">
                    {!! csrf_field() !!}

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Danh mục</label>
                        <div class="col-sm-6">
                            <select name="category_id" class="categories" >
                                <option value=""></option>
                                @foreach ($categoriesList as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Nhà SX</label>
                        <div class="col-sm-6">
                            <select name="manufacturer_id" class="manufactures" >
                                <option value=""></option>
                                @foreach ($manufacturersList as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
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

                    <label class="control-label no-padding-right">Các sản phẩm con</label>
                    <br>
                    <div>
                        <table class="table hoverTable" id="products-table">
                            <thead>
                            <th>ID</th>
                            <th>Tên sản phẩm</th>
                            <th>Sku</th>
                            <th>Số lượng</th>
                            <th>Thao tác</th>
                            </thead>
                            <tbody id = "bundleProducts">

                            </tbody>
                        </table>
                    </div>

                    <div class="clearfix form-actions">
                        <div class="col-md-offset-3 col-md-9">
                            <button type="submit" class="btn btn-success">
                                <i class="ace-icon fa fa-save bigger-110"></i>Lưu
                            </button>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModalProduct">
                                <i class="ace-icon fa fa-save bigger-110"></i>Thêm sản phẩm
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.min.js"></script>
@endsection

@section('inline_scripts')
    <script>
        $(function () {
            $(".categories").select2({
                placeholder: "-- Chọn danh mục --",
                allowClear: true,
                width:'100%',
            });
            $(".manufactures").select2({
                placeholder: "-- Chọn nhà sản xuất --",
                allowClear: true,
                width:'100%',
            });
        });
    </script>
@endsection
