@extends('layouts.app')

@section('content')
<div class="page-content" ng-controller="CategoryIndexController">
    <div class="page-header">
        <h1>
            Danh mục
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Danh sách
            </small>
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-6">
            <div class="form-inline">
                <label>Tìm kiếm: <input type="search" class="form-control" placeholder="Từ khóa tìm kiếm..." ng-model="searchForm.q" ng-change="refreshData()"></label>
            </div>
        </div>
        <div class="col-xs-6">
            @if (Sentinel::getUser()->hasAccess('categories.create'))
            <a class="btn btn-primary pull-right" ng-click="showAddCategoryModal()">
                <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                <span class="hidden-xs">Tạo danh mục</span>
            </a>
            @endif
        </div>
    </div>

    <div class="row p-t-10" ng-show="categoriesLoaded">
        <div class="col-xs-12">
            <table class="table table-striped table-bordered no-margin-bottom dataTable no-footer">
                <thead>
                    <tr>
                        <th class="sorting@{{ getSortingDirectionClassHeader('code', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('code')">Mã</th>
                        <th class="sorting@{{ getSortingDirectionClassHeader('name', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('name')">Tên</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="category in categories">
                        <td><a href="/categories/@{{ category.id }}/edit">@{{ category.code }}</a></td>
                        <td><a href="/categories/@{{ category.id }}/edit">@{{ category.name }}</a></td>
                        <td>
                            <span class="label label-success" ng-if="category.status == 1">Đang hoạt động</span>
                            <span class="label label-danger" ng-if="category.status == 0">Ngừng hoạt động</span>
                        </td>
                        <td>
                            @if ($currentUser->hasAccess('categories.margins.index'))
                            <button class="btn btn-white btn-warning btn-sm" ng-click="showEditMarginsModal(category)">Quản lý Margin</button>
                            @endif
                            @if ($currentUser->hasAccess('categories.edit'))
                            <a class="btn btn-white btn-info btn-sm" href="/categories/@{{ category.id }}/edit">Sửa</a>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <ul uib-pagination boundary-link-numbers="true" rotate="true" max-size="3" total-items="totalItems" items-per-page="@{{ searchForm.limit }}" ng-model="searchForm.page" ng-change="refreshData()" class="pagination"></ul>
        </div>
    </div>

    <!-- Show Add Category Modal -->
    <div class="modal fade" id="modal-add-category" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Tạo danh mục</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" ng-show="addCategoryForm.errors.length > 0">
                        <ul>
                            <li ng-repeat="error in addCategoryForm.errors">@{{ error }}</li>
                        </ul>
                    </div>
                    <form role="form">
                        <div class="form-group">
                            <label for="code">Mã danh mục</label>
                            <input type="text" class="form-control" ng-model="addCategoryForm.code" placeholder="Mã danh mục" />
                            <span class="help-block">
                                Dài 3 kí tự bao gồm chữ cái và số. Dùng để sinh SKU.
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="name">Tên danh mục</label>
                            <input type="text" class="form-control" ng-model="addCategoryForm.name" placeholder="Tên danh mục" />
                        </div>

                        <div class="form-group">
                            <label for="name">Kích hoạt</label>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6" ng-model="addCategoryForm.status">
                                <span class="lbl"></span>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-info" ng-click="store()" ng-disabled="addCategoryForm.disabled"><i class="ace-icon fa fa-save bigger-110"></i> Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Show Edit Margins Modal -->
    <div class="modal fade" id="modal-edit-margins" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Quản lý Margin Danh mục @{{ editingCategory.name }}</h4>
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
                    <button type="button" class="btn btn-info" ng-click="updateMargins()" ng-disabled="marginsForm.disabled"><i class="ace-icon fa fa-save bigger-110"></i> Lưu</button>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
