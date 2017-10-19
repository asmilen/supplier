@extends('layouts.app')

@section('inline_scripts')
<script>
    var SUPPLIER_ID = {{ $supplier->id }};
</script>
@endsection

@section('content')
<div class="page-content" ng-controller="SupplierEditController">
    <div class="page-header">
        <h1>
            Nhà cung cấp
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Thay đổi
            </small>
        </h1>
    </div><!-- /.page-header -->

    <div class="row">
        <div class="col-sm-6">
        </div>
        <div class="col-sm-6">
            @if (Sentinel::getUser()->hasAccess('suppliers.index'))
            <p class="pull-right">
                <a class="btn btn-primary" href="{{ url('/suppliers') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </p>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <form role="form">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Thông tin cơ bản
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-danger" ng-show="editSupplierForm.errors.length > 0">
                            <ul>
                                <li ng-repeat="error in editSupplierForm.errors">@{{ error }}</li>
                            </ul>
                        </div>
                        <div class="form-group">
                            <label>Tên nhà cung cấp<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="editSupplierForm.name" placeholder="Tên nhà cung cấp">
                        </div>

                        <div class="form-group">
                            <label>Tên đầy đủ<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="editSupplierForm.full_name" placeholder="Tên đầy đủ">
                        </div>

                        <div class="form-group">
                            <label>Mã nhà cung cấp<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="editSupplierForm.code" placeholder="Mã nhà cung cấp">
                        </div>

                        <div class="form-group">
                            <label>Phone<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="editSupplierForm.phone" placeholder="Phone">
                        </div>

                        <div class="form-group">
                            <label>Fax</label>
                            <input type="text" class="form-control" ng-model="editSupplierForm.fax" placeholder="Fax">
                        </div>

                        <div class="form-group">
                            <label>Email<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="editSupplierForm.email" placeholder="Email">
                        </div>

                        <div class="form-group">
                            <label>Website</label>
                            <input type="text" class="form-control" ng-model="editSupplierForm.website" placeholder="Website">
                        </div>

                        <div class="form-group">
                            <label>Mã số thuế<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="editSupplierForm.tax_number" placeholder="Mã số thuế">
                        </div>

                        <div class="form-group">
                            <label>Loại hóa đơn</label>
                            <select class="form-control" ng-model="editSupplierForm.type">
                                <option value="0">Hóa đơn Trực tiếp</option>
                                <option value="1">Hóa đơn Gián tiếp</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Loại hàng cung cấp</label>
                            <select class="form-control" ng-model="editSupplierForm.sup_type">
                                <option value="1">Hàng mua</option>
                                <option value="2">Kí gửi</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Thời gian hiệu lực giá (ngày)</label>
                            <input type="text" class="form-control" ng-model="editSupplierForm.price_active_time" placeholder="Thời gian hiệu lực giá (ngày)">
                        </div>

                        <div class="form-group">
                            <label>Kích hoạt</label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6" ng-model="editSupplierForm.status">
                                    <span class="lbl"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-info" ng-click="update()" ng-disabled="editSupplierForm.disabled">
                                <i class="ace-icon fa fa-save bigger-110"></i> Lưu
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
