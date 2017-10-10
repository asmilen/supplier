@extends('layouts.app')

@section('content')
<div class="page-content" ng-controller="ProductIndexController">
    <div class="page-header">
        <h1>
            Sản phẩm
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
                <a class="btn btn-primary" href="{{ route('products.create') }}">
                    <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                    <span class="hidden-xs">Tạo sản phẩm</span>
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
                                    <select class="select2" ng-model="searchForm.category_id" select2>
                                        <option value="">- Chọn danh mục -</option>
                                        @foreach ($categoriesList as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select class="select2" ng-model="searchForm.manufacturer_id" select2>
                                        <option value="">- Chọn nhà SX -</option>
                                        @foreach ($manufacturersList as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
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

    <div class="row p-t-10" ng-show="productsLoaded">
        <div class="col-xs-12">
            <div class="dataTables_wrapper form-inline no-footer">
                <table class="table table-striped table-bordered table-hover no-margin-bottom no-border-top dataTable no-footer">
                    <thead>
                        <tr>
                            <th class="sorting@{{ getSortingDirectionClassHeader('id', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('id')">ID</th>
                            <th class="sorting@{{ getSortingDirectionClassHeader('sku', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('sku')">SKU</th>
                            <th class="sorting@{{ getSortingDirectionClassHeader('name', searchForm.sorting, searchForm.direction) }}" ng-click="updateSorting('name')">Tên</th>
                            <th>Danh mục</th>
                            <th>Nhà SX</th>
                            <th>Kênh bán hàng</th>
                            <th>Ảnh</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="product in products">
                            <td><a href="/products/@{{ product.id }}/edit">@{{ product.id }}</a></td>
                            <td><a href="/products/@{{ product.id }}/edit">@{{ product.sku }}</a></td>
                            <td><a href="/products/@{{ product.id }}/edit">@{{ product.name }}</a></td>
                            <td>@{{ product.category.name }}</td>
                            <td>@{{ product.manufacturer.name }}</td>
                            <td>@{{ channelText(product.channel) }}</td>
                            <td><img ng-if="product.image" ng-src="@{{ product.image }}" data-lity="@{{ product.image }}" style="height: 80px;"></td>
                            <td>
                                <span class="label label-success" ng-if="product.status">Đang hoạt động</span>
                                <span class="label label-danger" ng-if="! product.status">Ngừng hoạt động</span>
                            </td>
                            <td>
                                @if ($currentUser->hasAccess('products.edit'))
                                <a class="btn btn-white btn-info btn-sm" href="/products/@{{ product.id }}/edit">Sửa</a>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="dataTables_info">Hiển thị từ @{{ (searchForm.page - 1) * searchForm.limit + 1 }} đến @{{ searchForm.page * searchForm.limit }} của @{{ totalItems }} sản phẩm</div>
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
