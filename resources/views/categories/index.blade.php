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
            <a href="{{ route('categories.index') }}">Danh mục</a>
        </li>
        <li class="active">Danh sách</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content" ng-controller="CategoryIndexController">
    <div class="page-header">
        <h1>
            Danh mục
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Danh sách
            </small>
            <a class="btn btn-primary pull-right" href="{{ route('categories.create') }}">
                <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                <span class="hidden-xs">Thêm</span>
            </a>
        </h1>
    </div><!-- /.page-header -->
    <div class="row" ng-show="categoriesLoaded">
        <div class="col-xs-12">
            <table id="dataTables-categories" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên</th>
                        <th>Trạng thái</th>
                        <th>Margin</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="category in categories">
                        <td>@{{ category.code }}</td>
                        <td>@{{ category.name }}</td>
                        <td>
                            <i class="@{{ category.status ? 'ace-icon fa bigger-130 fa-check-circle-o green' : 'ace-icon fa bigger-130 fa-times-circle-o red'}}"></i>
                        </td>
                        <td>
                            @if ($currentUser->hasAccess('categories.margins'))
                                <span class="orange" style="cursor: pointer;" ng-click="showEditMarginsModal(category)"><i class="ace-icon fa fa-anchor bigger-130"></i></span>
                            @endif
                        </td>
                        <td>
                            @if ($currentUser->hasAccess('categories.show'))
                                <a class="blue" href="/categories/@{{ category.id }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>
                            @endif
                            @if ($currentUser->hasAccess('categories.edit'))
                                <a class="green" href="/categories/@{{ category.id }}/edit"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Show Edit Margins Modal -->
    <div class="modal fade" id="modal-edit-margins" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Quản lý Margin Danh mục @{{ marginCategoryName }}</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" ng-show="marginsForm.errors.length > 0">
                        <ul>
                            <li ng-repeat="error in marginsForm.errors">@{{ error }}</li>
                        </ul>
                    </div>
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Miền Bắc (%)</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" ng-model="marginsForm.margins[1]" placeholder="Miền Bắc" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Miền Trung (%)</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" ng-model="marginsForm.margins[2]" placeholder="Miền Trung" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Miền Nam (%)</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" ng-model="marginsForm.margins[3]" placeholder="Miền Nam" />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" ng-click="updateMargins()" ng-disabled="marginsForm.disabled"><i class="fa fa-save"></i> Cập nhật</button>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
