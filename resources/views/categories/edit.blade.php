@extends('layouts.app')

@section('inline_scripts')
<script>
    var CATEGORY_ID = {{ $category->id }};
</script>
@endsection

@section('content')
<div class="page-content" ng-controller="CategoryEditController">
    <div class="page-header">
        <h1>
            Danh mục
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                {{ $category->name }}
            </small>
        </h1>
    </div><!-- /.page-header -->

    <div class="row">
        <div class="col-xs-6">
        </div>
        <div class="col-xs-6">
            @if (Sentinel::getUser()->hasAccess('categories.create'))
            <p class="pull-right">
                <a class="btn btn-primary" href="{{ route('categories.index') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </p>
            @endif
        </div>
    </div>

    <div class="row" ng-if="categoryLoaded">
        <div class="col-xs-12">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a data-toggle="tab" href="#tab-general">Thông tin chung</a>
                </li>

                <li>
                    <a data-toggle="tab" href="#tab-attributes">Quản lý Thuộc tính</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="tab-general" class="tab-pane fade in active">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="alert alert-success" ng-if="editCategoryForm.successful">
                                Cập nhật danh mục thành công.
                            </div>
                            <div class="alert alert-danger" ng-show="editCategoryForm.errors.length > 0">
                                <ul>
                                    <li ng-repeat="error in editCategoryForm.errors">@{{ error }}</li>
                                </ul>
                            </div>
                            <form class="form-horizontal" role="form">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">Mã danh mục</label>
                                    <div class="col-sm-6">
                                        <p class="form-control-static"><strong>@{{ category.code }}</strong></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">Tên danh mục</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" placeholder="Tên danh mục" ng-model="editCategoryForm.name">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">Kích hoạt</label>
                                    <div class="col-sm-6">
                                        <label>
                                            <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6" ng-model="editCategoryForm.status">
                                            <span class="lbl"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn btn-info" ng-click="update()" ng-disabled="editCategoryForm.disabled">
                                            <i class="ace-icon fa fa-save bigger-110"></i> Lưu
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="tab-attributes" class="tab-pane fade">
                    <p>Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.</p>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
