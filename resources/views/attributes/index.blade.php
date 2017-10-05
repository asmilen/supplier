@extends('layouts.app')

@section('content')
<div class="page-content" ng-controller="AttributeIndexController">
    <div class="page-header">
        <h1>
            Thuộc tính
        </h1>
    </div><!-- /.page-header -->

    <div class="row">
        <div class="col-xs-6">
            <div class="form-inline">
                <label>Tìm kiếm: <input type="search" class="form-control" placeholder="Từ khóa tìm kiếm..." ng-model="searchForm.q" ng-change="refreshData()"></label>
            </div>
        </div>
    </div>

    <div class="row p-t-10">
        <div class="col-sm-8">
            <table class="table table-striped table-bordered no-margin-bottom dataTable no-footer">
                <thead>
                    <tr>
                        <th class="sorting@{{ getSortingDirectionClassHeader('slug', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('slug')">Mã</th>
                        <th class="sorting@{{ getSortingDirectionClassHeader('name', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('name')">Tên</th>
                        <th class="sorting@{{ getSortingDirectionClassHeader('frontend_input', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('frontend_input')">Kiểu nhập dữ liệu</th>
                        <th class="sorting@{{ getSortingDirectionClassHeader('backend_type', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('backend_type')">Kiểu dữ liệu</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="attribute in attributes">
                        <td>@{{ attribute.slug }}</td>
                        <td>@{{ attribute.name }}</td>
                        <td>@{{ attribute.frontend_input }}</td>
                        <td>@{{ attribute.backend_type }}</td>
                    </tr>
                </tbody>
            </table>

            <ul uib-pagination boundary-link-numbers="true" rotate="true" max-size="3" total-items="totalItems" items-per-page="@{{ searchForm.limit }}" ng-model="searchForm.page" ng-change="refreshData()" class="pagination"></ul>
        </div>

        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">Tạo thuộc tính</div>
                <div class="panel-body">
                    <div class="alert alert-danger" ng-show="addAttributeForm.errors.length > 0">
                        <ul>
                            <li ng-repeat="error in addAttributeForm.errors">@{{ error }}</li>
                        </ul>
                    </div>
                    <form role="form">
                        <div class="form-group">
                            <label for="slug">Mã thuộc tính</label>
                            <input type="text" class="form-control" ng-model="addAttributeForm.slug">
                            <span class="help-block"><small>Dùng nội bộ. Phải là duy nhất và không có dấu cách. Độ dài tối đa nhỏ hơn 30 kí tự.</small></span>
                        </div>
                        <div class="form-group">
                            <label for="name">Tên hiển thị</label>
                            <input type="text" class="form-control" ng-model="addAttributeForm.name">
                        </div>
                        <div class="form-group">
                            <label for="frontend_input">Kiểu nhập dữ liệu</label>
                            <select class="form-control" ng-model="addAttributeForm.frontend_input" ng-change="mapBackendType()">
                                <option ng-repeat="(key, value) in frontendInputs" value="@{{ key }}">@{{ value }}</option>
                            </select>
                        </div>
                        <div class="form-group" ng-show="addAttributeForm.frontend_input == 'text'">
                            <label for="backend_type">Kiểu dữ liệu</label>
                            <select class="form-control" ng-model="addAttributeForm.backend_type">
                                <option ng-repeat="(key, value) in backendTypes" value="@{{ key }}">@{{ value }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-success" ng-click="addAttribute()" ng-disabled="addAttributeForm.disabled"><i class="fa fa-save"></i> Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
