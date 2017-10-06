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
                        <td ng-if="editingAttribute.id != attribute.id" ng-click="showEditForm(attribute)">@{{ attribute.name }}</td>
                        <td ng-if="editingAttribute.id == attribute.id">
                            <form class="form-inline">
                                <input type="text" ng-model="editAttributeForm.name">
                                <button class="btn btn-sm btn-primary" ng-click="update()" ng-disabled="editAttributeForm.disabled">Lưu</button>
                                <button class="btn btn-white btn-sm btn-default" ng-click="cancelEditing()">Hủy</button>
                            </form>
                        </td>
                        <td>
                            @{{ attribute.frontend_input }}
                            <button class="btn btn-white btn-sm btn-info" ng-if="attribute.frontend_input == 'select' || attribute.frontend_input == 'multiselect'" ng-click="showEditOptionsModal(attribute)">
                                Quản lý Option
                            </button>
                        </td>
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
                    <div class="alert alert-success" ng-if="addAttributeForm.successful">
                        Tạo thuộc tính thành công.
                    </div>
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
                            <button class="btn btn-success" ng-click="store()" ng-disabled="addAttributeForm.disabled"><i class="ace-icon fa fa-save bigger-110"></i> Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Show Edit Options Modal -->
    <div class="modal fade" id="modal-edit-options" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Quản lý Option thuộc tính @{{ editingOptionsAttribute.name }}</h4>
                </div>
                <div class="modal-body">
                    <form class="form-inline p-b-10">
                        <input type="text" class="form-control" ng-model="addOptionForm.value">
                        <button class="btn btn-success btn-sm" title="Thêm" ng-click="addOption()" ng-disabled="addOptionForm.value == '' || addOptionForm.disabled"><i class="ace-icon fa fa-plus"></i></button>
                    </form>
                    <table class="table table-hover">
                        <thead>
                            <th>Giá trị</th>
                            <th></th>
                        </thead>
                        <tbody>
                            <tr ng-repeat="item in options track by item.id">
                                <td ng-if="editingOption.id != item.id" ng-click="showEditOptionForm(item)">@{{ item.value }}</td>
                                <td ng-if="editingOption.id == item.id">
                                    <form class="form-inline">
                                        <input type="text" ng-model="editOptionForm.value">
                                        <button class="btn btn-info btn-sm" title="Lưu" ng-click="updateOption()" ng-disabled="editOptionForm.disabled">Lưu</button>
                                        <button class="btn btn-white btn-sm btn-default" ng-click="cancelEditingOption()">Hủy</button>
                                    </form>
                                </td>
                                <td>
                                    <!-- <button class="btn btn-danger btn-sm btn-white" title="Xóa" ng-click="deleteOption(item)"><i class="fa fa-trash"></i></button> -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
