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
                    </div>
                </div>
            </form>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection

@section('inline_scripts')
<script>
var PRODUCT_ID = {{ $product->id }};
</script>
@endsection
