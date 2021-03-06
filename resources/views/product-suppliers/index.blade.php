@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="/vendor/ace/assets/css/datepicker.css" />
@endsection

@section('content')
<!-- #section:basics/content.breadcrumbs -->
<div class="breadcrumbs" id="breadcrumbs">
    <script type="text/javascript">
        try {
            ace.settings.check('breadcrumbs', 'fixed')
        } catch (e) {
        }
    </script>

    <ul class="breadcrumb">
        <li>
            <i class="ace-icon fa fa-home home-icon"></i>
            <a href="{{ url('/dashboard') }}">Dashboard</a>
        </li>
        <li>
            <a href="{{ url('/product-suppliers') }}">Sản phẩm - Nhà cung cấp</a>
        </li>
        <li class="active">Danh sách</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content" ng-controller="ProductSupplierIndexController">
    <div class="page-header">
        <h1>
            Sản phẩm - Nhà cung cấp
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Danh sách
            </small>
        </h1>
    </div><!-- /.page-header -->

    <div class="row">
        <div class="col-sm-6">
            <p class="text-left">
                <button class="btn btn-primary" ng-click="showAddProductSupplierModal()">
                    <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                    <span class="hidden-xs">Thêm</span>
                </button>
                <button class="btn btn-xlg btn-white btn-default" ng-click="showImportFromExcelModal()">
                    <i class="ace-icon fa fa-cloud-upload" aria-hidden="true"></i>
                    <span class="hidden-xs">Import</span>
                </button>
                <button class="btn btn-xlg btn-white btn-success" ng-click="exportToExcel()" ng-disabled="exportForm.disabled">
                    <i class="ace-icon fa fa-cloud-download" aria-hidden="true"></i>
                    <span class="hidden-xs">Export</span>
                </button>
                <button class="btn btn-xlg btn-white btn-info2" ng-click="showUpdateValidTimeModal()" >
                    <i class="ace-icon fa fa-calendar-check-o" aria-hidden="true"></i>
                    <span class="hidden-xs">Cập nhật hiệu lực giá</span>
                </button>
            </p>
        </div>
        <div class="col-sm-6">
            <p class="text-right">
                @if (Sentinel::getUser()->hasAccess('product-suppliers.update-all-prices-to-magento'))
                <button class="btn btn-info text-right" ng-click="showUpdatePricesToMagentoModal()">
                    <i class="ace-icon fa fa-flask" aria-hidden="true"></i>
                    <span class="hidden-xs">Cập nhật giá sang Magento</span>
                </button>
                @endif
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
                                <div class="col-sm-2" style="width:13%">
                                    <select class="select2" ng-model="searchProductSupplierForm.category_id" ng-change="refreshData()" select2>
                                        <option value="">-- Danh mục --</option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select class="select2" ng-model="searchProductSupplierForm.manufacturer_id" ng-change="refreshData()" select2>
                                        <option value="">-- Nhà sản xuất --</option>
                                        @foreach ($manufacturers as $manufacturer)
                                        <option value="{{ $manufacturer->id }}">{{ $manufacturer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select class="select2" ng-model="searchProductSupplierForm.supplier_id" ng-change="refreshData()" select2>
                                        <option value="">-- Nhà cung cấp --</option>
                                        @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <select class="select2" ng-model="searchProductSupplierForm.region_id" ng-change="refreshData()" select2>
                                        <option value="">-- Miền --</option>
                                        @foreach (config('teko.regions') as $key => $region)
                                            <option value="{{ $key }}">{{ $region }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2" style="width:11%">
                                    <input type="text"  placeholder="Tên hoặc SKU sản phẩm" ng-model="searchProductSupplierForm.q" ng-change="refreshData()" />
                                </div>
                                <div class="col-sm-2" style="width:11%">
                                    <input type="text"  placeholder="Id sản phẩm" ng-model="searchProductSupplierForm.product_id" ng-change="refreshData()" />
                                </div>
                                <div class="col-sm-2" style="width:11%">
                                    <select class="select2" ng-model="searchProductSupplierForm.state" ng-change="refreshData()" select2>
                                        <option value="">-- Trạng thái hàng --</option>
                                        @foreach (config('teko.product.state') as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <button type="button" class="btn btn-purple btn-sm" ng-click="refreshData()">
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

    <div class="row">
        <div class="col-xs-12">
            <table class="table table-striped table-bordered table-hover no-margin-bottom no-border-top dataTable no-footer" ng-if="productSuppliersLoaded">
                <thead>
                    <tr>
                        <th></th>
                        <th class="hidden-sm hidden-xs">Danh mục</th>
                        <th class="hidden-sm hidden-xs">Nhà sản xuất</th>
                        <th>SKU</th>
                        <th class="sorting@{{ getSortingDirectionClassHeader('name') }}" ng-click="updateSorting('name')">Tên</th>
                        <th>NCC</th>
                        <th>Miền</th>
                        <th class="sorting@{{ getSortingDirectionClassHeader('import_price') }}" ng-click="updateSorting('import_price')">Giá nhập</th>
                        <th class="hidden-sm hidden-xs sorting@{{ getSortingDirectionClassHeader('from_date') }}" ng-click="updateSorting('from_date')">Hiệu lực từ</th>
                        <th class="hidden-sm hidden-xs sorting@{{ getSortingDirectionClassHeader('to_date') }}" ng-click="updateSorting('to_date')">Hiệu lực đến</th>
                        <th class="hidden-sm hidden-xs sorting@{{ getSortingDirectionClassHeader('min_quantity') }}" ng-click="updateSorting('min_quantity')">Số lượng tối thiểu</th>
                        <th class="hidden-sm hidden-xs sorting@{{ getSortingDirectionClassHeader('price_recommend') }}" ng-click="updateSorting('price_recommend')">Giá bán khuyến nghị</th>
                        <th class="hidden-sm hidden-xs sorting@{{ getSortingDirectionClassHeader('state') }}" ng-click="updateSorting('state')">Tình trạng</th>
                        <th class="hidden-sm hidden-xs sorting@{{ getSortingDirectionClassHeader('updated_by') }}" ng-click="updateSorting('updated_by')">Người cập nhật</th>
                        <th class="hidden-sm hidden-xs sorting@{{ getSortingDirectionClassHeader('updated_at') }}" ng-click="updateSorting('updated_at')">Cập nhật lần cuối</th>
                    </tr>
                </thead>

                <tbody>
                    <tr ng-repeat="item in productSuppliers">
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-xs btn-info" ng-click="showEditProductSupplierModal(item)">
                                    <i class="ace-icon fa fa-pencil bigger-120"></i>
                                </button>
                            </div>
                        </td>
                        <td class="hidden-sm hidden-xs">@{{ item.product.category.name }}</td>
                        <td class="hidden-sm hidden-xs">@{{ item.product.manufacturer.name }}</td>
                        <td>@{{ item.product.sku }}</td>
                        <td>@{{ item.product.name }}</td>
                        <td>@{{ item.supplier.name }}</td>
                        <td>@{{ regionText(item.region_id) }}</td>
                        <td class="text-right">@{{ item.import_price | number:0 }}</td>
                        <td class="text-right hidden-sm hidden-xs">@{{ item.from_date }}</td>
                        <td class="text-right hidden-sm hidden-xs">@{{ item.to_date }}</td>
                        <td class="text-right hidden-sm hidden-xs">@{{ item.min_quantity | number:0 }}</td>
                        <td class="text-right hidden-sm hidden-xs">@{{ item.price_recommend | number:0 }}</td>
                        <td class="hidden-sm hidden-xs">@{{ stateText(item.state) }}</td>
                        <td class="hidden-sm hidden-xs">@{{ item.updater.name ? item.updater.name : item.creater.name }}</td>
                        <td class="text-right hidden-sm hidden-xs">@{{ item.updated_at }}</td>
                    </tr>
                </tbody>
            </table>

            <ul uib-pagination boundary-link-numbers="true" rotate="true" max-size="3" total-items="searchProductSupplierForm.total_items" items-per-page="@{{ searchProductSupplierForm.limit }}" ng-model="searchProductSupplierForm.page" ng-change="refreshData()" class="pagination"></ul>
        </div>
    </div>

    <div class="modal fade" id="modal-add-product-supplier" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Thêm giá nhập Sản phẩm theo Nhà cung cấp</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success" ng-if="addProductSupplierForm.success">
                        Thêm giá nhập theo nhà cung cấp thành công.
                    </div>
                    <div class="alert alert-danger" ng-show="addProductSupplierForm.errors.length > 0">
                        <ul>
                            <li ng-repeat="error in addProductSupplierForm.errors">@{{ error }}</li>
                        </ul>
                    </div>
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Sản phẩm</label>
                            <div class="col-sm-9">
                                <p class="form-control-static">
                                    <a class="action-link" ng-click="showSelectProductModal()">@{{ addProductSupplierForm.product_id ? addProductSupplierForm.product_name : 'Chọn sản phẩm' }}</a>
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Nhà cung cấp</label>
                            <div class="col-sm-9">
                                <p class="form-control-static">
                                    <a class="action-link" ng-click="showSelectSupplierModal()">@{{ addProductSupplierForm.supplier_id ? addProductSupplierForm.supplier_name : 'Chọn nhà cung cấp' }}</a>
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Miền</label>
                            <div class="col-sm-9">
                                @foreach (config('teko.regions') as $k => $v)
                                    <div class="checkbox">
                                        <label>
                                            <input name="form-field-checkbox" type="checkbox" class="ace" ng-model="addProductSupplierForm.regions[{{ $k }}]" value="{{ $k }}" />
                                            <span class="lbl"> {{ $v }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Giá nhập (có VAT)</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" ng-model="addProductSupplierForm.import_price" placeholder="Giá nhập (có VAT)" format="number" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Hiệu lực giá</label>
                            <div class="col-sm-9">
                                <div class="input-daterange input-group">
                                    <input type="text" class="input-sm form-control" ng-model="addProductSupplierForm.from_date" placeholder="Từ" readonly/>
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="input-sm form-control" ng-model="addProductSupplierForm.to_date" placeholder="Đến" readonly/>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Số lượng tối thiểu</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" ng-model="addProductSupplierForm.min_quantity" placeholder="Số lượng tối thiểu" format="number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Giá bán khuyến nghị</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" ng-model="addProductSupplierForm.price_recommend" placeholder="Giá bán khuyến nghị" format="number">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" ng-click="addProductSupplier()" ng-disabled="addProductSupplierForm.disabled"><i class="fa fa-save"></i> Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-select-product" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Chọn sản phẩm</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <form class="form-inline">
                                <input type="text" class="input-large" placeholder="ID hoặc SKU" ng-model="productsListForm.q" ng-change="getProductsList(productsListForm.page)">
                                <button type="button" class="btn btn-info btn-sm" ng-click="getProductsList(productsListForm.page)">Tìm kiếm</button>
                            </form>

                            <h3 class="header smaller lighter blue"></h3>

                            <table class="table table-bordered table-hover" ng-show="productsList.length > 0">
                                <thead>
                                    <tr>
                                        <th>Chọn</th>
                                        <th>ID</th>
                                        <th>SKU</th>
                                        <th>Tên</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="product in productsList">
                                        <td>
                                            <button class="btn btn-xs btn-success" ng-click="selectProduct(product)">
                                                <i class="ace-icon fa fa-check bigger-120"></i>
                                            </button>
                                        </td>
                                        <td>@{{ product.id }}</td>
                                        <td>@{{ product.sku }}</td>
                                        <td>@{{ product.name }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <ul uib-pagination boundary-link-numbers="true" rotate="true" max-size="3" total-items="productsListForm.total_items" items-per-page="@{{ productsListForm.limit }}" ng-model="productsListForm.page" ng-change="getProductsList()" class="pagination"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-select-supplier" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Chọn nhà cung cấp</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <form class="form-inline">
                                <input type="text" class="input-large" placeholder="ID hoặc Tên" ng-model="suppliersListForm.q" ng-change="getSuppliersList(suppliersListForm.page)">
                                <button type="button" class="btn btn-info btn-sm" ng-click="getSuppliersList(suppliersListForm.page)">Tìm kiếm</button>
                            </form>

                            <h3 class="header smaller lighter blue"></h3>

                            <table class="table table-bordered table-hover" ng-show="suppliersList.length > 0">
                                <thead>
                                    <tr>
                                        <th>Chọn</th>
                                        <th>ID</th>
                                        <th>Tên</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="supplier in suppliersList">
                                        <td>
                                            <button class="btn btn-xs btn-success" ng-click="selectSupplier(supplier)">
                                                <i class="ace-icon fa fa-check bigger-120"></i>
                                            </button>
                                        </td>
                                        <td>@{{ supplier.id }}</td>
                                        <td>@{{ supplier.name }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <ul uib-pagination boundary-link-numbers="true" rotate="true" max-size="3" total-items="suppliersListForm.total_items" items-per-page="@{{ suppliersListForm.limit }}" ng-model="suppliersListForm.page" ng-change="getSuppliersList()" class="pagination"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-update-prices-to-magento" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Cập nhật giá sang Magento</h4>
                </div>
                <div class="modal-body">
                    <p>Cập nhật giá sang Magento sẽ mất thời gian chạy ngầm.</p>
                    <p>Bạn có đồng ý cập nhật giá không?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" ng-click="updatePricesToMagento()" ng-disabled="updatePricesToMagentoForm.disabled"><i class="fa fa-save"></i> Cập nhật</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-edit-product-supplier" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Sửa giá nhập Sản phẩm (@{{ editProductSupplier.product.name }})</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" ng-show="editProductSupplierForm.errors.length > 0">
                        <ul>
                            <li ng-repeat="error in editProductSupplierForm.errors">@{{ error }}</li>
                        </ul>
                    </div>
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Sản phẩm</label>
                            <div class="col-sm-9">
                                <p class="form-control-static">@{{ editProductSupplier.product.name }}</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Nhà cung cấp</label>
                            <div class="col-sm-9">
                                <p class="form-control-static">@{{ editProductSupplier.supplier.name }}</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Miền</label>
                            <div class="col-sm-9">
                                <p class="form-control-static">@{{ regionText(editProductSupplier.region_id) }}</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Giá nhập (có VAT)</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" ng-model="editProductSupplierForm.import_price" placeholder="Giá nhập (có VAT)" format="number" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Hiệu lực giá</label>
                            <div class="col-sm-9">
                                <div class="input-daterange input-group">
                                    <input type="text" class="input-sm form-control" ng-model="editProductSupplierForm.from_date" placeholder="Từ" readonly/>
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="input-sm form-control" ng-model="editProductSupplierForm.to_date" placeholder="Đến" readonly/>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Số lượng tối thiểu</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" ng-model="editProductSupplierForm.min_quantity" placeholder="Số lượng tối thiểu" format="number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Giá bán khuyến nghị</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" ng-model="editProductSupplierForm.price_recommend" placeholder="Giá bán khuyến nghị" format="number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Trạng thái hàng</label>
                            <div class="col-sm-9">
                                <select class="form-control" ng-model="editProductSupplierForm.state">
                                    <option value="">-- Trạng thái hàng --</option>
                                    @foreach (config('teko.product.state') as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" ng-click="updateProductSupplier(editProductSupplier)" ng-disabled="editProductSupplierForm.disabled"><i class="fa fa-save"></i> Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-import-from-excel" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Import giá nhập - NCC</h4>
                </div>
                <form class="form-horizontal" role="form" action="{{ url('/suppliers/importExcel') }}" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Chọn file</label>
                            <div class="col-sm-9">
                                <input type="file" name="file" id="fileUpload">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-update-valid-time" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Cập nhật hiệu lực giá sản phẩm</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" ng-show="updateValidTimeForm.errors.length > 0">
                        <ul>
                            <li ng-repeat="error in updateValidTimeForm.errors">@{{ error }}</li>
                        </ul>
                    </div>
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Hiệu lực giá</label>
                            <div class="col-sm-9">
                                <div class="input-daterange input-group">
                                    <input type="text" class="input-sm form-control" ng-model="updateValidTimeForm.from_date" placeholder="Từ" readonly/>
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="input-sm form-control" ng-model="updateValidTimeForm.to_date" placeholder="Đến" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Tổng số sản phẩm</label>
                            <div class="col-sm-3">
                                <label class="col-sm-3 control-label no-padding-right"> <strong>@{{ searchProductSupplierForm.total_items }} </strong> </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success" ng-click="updateValidTime()" ng-disabled="updateValidTimeForm.disabled"><i class="fa fa-save"></i> Cập nhật</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="/vendor/ace/assets/js/date-time/bootstrap-datepicker.js"></script>
@endsection
