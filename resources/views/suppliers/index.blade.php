@extends('layouts.app')

@section('content')
<div class="page-content" ng-controller="SupplierIndexController">
    <div class="page-header">
        <h1>
            Nhà cung cấp
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Danh sách
            </small>
        </h1>
    </div><!-- /.page-header -->

    <div class="row">
        <div class="col-xs-6">
        </div>
        <div class="col-xs-6">
            <p class="pull-right">
                <a class="btn btn-primary" href="{{ route('suppliers.create') }}">
                    <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                    <span class="hidden-xs">Tạo nhà cung cấp</span>
                </a>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="widget-box">
                <div class="widget-header">
                    <h5 class="widget-title">Lọc</h5>
                </div>

                <div class="widget-body">
                    <div class="widget-main">
                        <form class="form-inline" id="search-form">
                            <div class="row">
                                <div class="col-sm-2">
                                    <select class="form-control" ng-model="searchForm.status" style="width: 100%">
                                        <option value="">--Chọn Trạng thái--</option>
                                        <option value="active">Kích hoạt</option>
                                        <option value="inactive">Không kích hoạt</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control" ng-model="searchForm.q" placeholder="Từ khóa tìm kiếm" style="width: 100%" />
                                </div>
                                <div class="col-sm-2">
                                    <button type="submit" class="btn btn-purple btn-sm" ng-click="refreshData()">
                                        <span class="ace-icon fa fa-search icon-on-right bigger-110"></span> Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row p-t-10" ng-show="suppliersLoaded">
        <div class="col-xs-12">
            <div class="dataTables_wrapper form-inline no-footer">
                <table class="table table-striped table-bordered table-hover no-margin-bottom no-border-top dataTable no-footer">
                    <thead>
                        <tr>
                            <th class="sorting@{{ getSortingDirectionClassHeader('id', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('id')">ID</th>
                            <th class="sorting@{{ getSortingDirectionClassHeader('name', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('name')">Tên</th>
                            <th>Địa chỉ</th>
                            <th>Địa bàn cung cấp</th>
                            <th>Mã số thuế</th>
                            <th>Loại nhà cung cấp</th>
                            <th>Thông tin về người đại diện của NCC</th>
                            <th>Thời gian hiệu lực giá nhập</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="supplier in suppliers">
                            <td><a href="/suppliers/@{{ supplier.id }}/edit">@{{ supplier.id }}</a></td>
                            <td><a href="/suppliers/@{{ supplier.id }}/edit">@{{ supplier.name }}</a></td>
                            <td>@{{ supplier.address.address }}</td>
                            <td>@{{ supplier.supplier_supported_province[0].name }}</td>
                            <td>@{{ supplier.tax_number }}</td>
                            <td>
                                <span ng-if="supplier.sup_type == 1">Hàng mua</span>
                                <span ng-if="supplier.sup_type == 2">Ký gửi</span>
                                <span ng-if="supplier.sup_type != 1 && supplier.sup_type != 2">N/A</span>
                            </td>
                            <td>@{{ supplier.address.contact_name }} - @{{ supplier.address.contact_phone }}</td>
                            <td>@{{ supplier.price_active_time / 24 }} ngày</td>
                            <td>
                                <span class="label label-success" ng-if="supplier.status">Đang hoạt động</span>
                                <span class="label label-danger" ng-if="! supplier.status">Ngừng hoạt động</span>
                            </td>
                            <td>
                                @if ($currentUser->hasAccess('suppliers.edit'))
                                <a class="btn btn-white btn-info btn-sm" href="/suppliers/@{{ supplier.id }}/edit">Sửa</a>
                                @endif
                            </td>
                        </tr>
                        <tr ng-if="totalItems == 0">
                            <td colspan="10">Không tìm thấy kết quả tương ứng nào.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="dataTables_info">Hiển thị từ @{{ (totalItems > 0) ? (searchForm.page - 1) * searchForm.limit + 1 : 0 }} đến @{{ (searchForm.page * searchForm.limit < totalItems) ? searchForm.page * searchForm.limit : totalItems }} của @{{ totalItems }} sản phẩm <span ng-if="all > totalItems">(Lọc từ @{{ countAll }} sản phẩm)</span></div>
                    </div>
                    <div class="col-xs-6">
                        <div class="dataTables_paginate paging_simple_numbers">
                            <ul uib-pagination boundary-link-numbers="true" rotate="true" max-size="3" total-items="totalItems" items-per-page="@{{ searchForm.limit }}" ng-model="searchForm.page" ng-change="refreshData()" class="pagination"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
