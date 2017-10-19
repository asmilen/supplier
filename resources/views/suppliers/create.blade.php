@extends('layouts.app')

@section('content')
<div class="page-content" ng-controller="SupplierCreateController">
    <div class="page-header">
        <h1>
            Nhà cung cấp
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Tạo mới
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
        <div class="col-sm-12">
            <div class="alert alert-danger" ng-show="addSupplierForm.errors.length > 0">
                <ul>
                    <li ng-repeat="error in addSupplierForm.errors">@{{ error }}</li>
                </ul>
            </div>
        </div>
        <form role="form">
            <div class="col-sm-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Thông tin cơ bản
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>Tên nhà cung cấp<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.name" placeholder="Tên nhà cung cấp">
                        </div>

                        <div class="form-group">
                            <label>Tên đầy đủ<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.full_name" placeholder="Tên đầy đủ">
                        </div>

                        <div class="form-group">
                            <label>Mã nhà cung cấp<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.code" placeholder="Mã nhà cung cấp">
                        </div>

                        <div class="form-group">
                            <label>Phone<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.phone" placeholder="Phone">
                        </div>

                        <div class="form-group">
                            <label>Fax</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.fax" placeholder="Fax">
                        </div>

                        <div class="form-group">
                            <label>Email<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.email" placeholder="Email">
                        </div>

                        <div class="form-group">
                            <label>Website</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.website" placeholder="Website">
                        </div>

                        <div class="form-group">
                            <label>Mã số thuế<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.tax_number" placeholder="Mã số thuế">
                        </div>

                        <div class="form-group">
                            <label>Loại hóa đơn</label>
                            <select class="form-control" ng-model="addSupplierForm.type">
                                <option value="0">Hóa đơn Trực tiếp</option>
                                <option value="1">Hóa đơn Gián tiếp</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Loại hàng cung cấp</label>
                            <select class="form-control" ng-model="addSupplierForm.sup_type">
                                <option value="1">Hàng mua</option>
                                <option value="2">Kí gửi</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Thời gian hiệu lực giá (ngày)</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.price_active_time" placeholder="Thời gian hiệu lực giá (ngày)">
                        </div>

                        <div class="form-group">
                            <label>Kích hoạt</label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6" ng-model="addSupplierForm.status">
                                    <span class="lbl"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Thông tin địa chỉ
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>Tỉnh / Thành phố<span class="asterisk">*</span></label>
                            <select class="select2" ng-model="addSupplierForm.province_id" ng-change="changeProvince()" select2>
                                <option value="0">- Tỉnh / Thành phố -</option>
                                <option ng-repeat="province in provinces" value="@{{ province.id }}">@{{ province.name }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Quận / Huyện<span class="asterisk">*</span></label>
                            <select class="select2" ng-model="addSupplierForm.district_id" select2>
                                <option value="0">- Quận / Huyện -</option>
                                <option ng-repeat="district in districts" value="@{{ district.district_id }}">@{{ district.name }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Địa chỉ<span class="asterisk">*</span></label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.address" placeholder="Địa chỉ">
                        </div>

                        <div class="form-group">
                            <label>Mã địa chỉ</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.addressCode" disabled placeholder="Mã địa chỉ">
                        </div>

                        <div class="form-group">
                            <label>Tên người liên hệ</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.contact_name" placeholder="Tên người liên hệ">
                        </div>

                        <div class="form-group">
                            <label>Contact Mobile</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.contact_mobile" placeholder="Contact Mobile">
                        </div>

                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.contact_phone" placeholder="Contact Phone">
                        </div>

                        <div class="form-group">
                            <label>Email liên hệ</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.contact_email" placeholder="Email liên hệ">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Tài khoản ngân hàng
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>Số tài khoản</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.bank_account" placeholder="Số tài khoản">
                        </div>

                        <div class="form-group">
                            <label>Chủ tài khoản</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.bank_account_name" placeholder="Chủ tài khoản">
                        </div>

                        <div class="form-group">
                            <label>Tên Ngân hàng</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.bank_name" placeholder="Tên ngân hàng">
                        </div>

                        <div class="form-group">
                            <label>Mã ngân hàng</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.bank_code" placeholder="Mã ngân hàng">
                        </div>

                        <div class="form-group">
                            <label>Chi nhánh ngân hàng</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.bank_branch" placeholder="Chi nhánh ngân hàng">
                        </div>

                        <div class="form-group">
                            <label>Tỉnh / Thành phố</label>
                            <input type="text" class="form-control" ng-model="addSupplierForm.bank_province" placeholder="Tỉnh / Thành phố">
                        </div>
                    </div>
                </div>
            </div>

            <div class="clearfix">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-info" ng-click="store()" ng-disabled="addSupplierForm.disabled">
                        <i class="ace-icon fa fa-save bigger-110"></i> Lưu
                    </button>
                </div>
            </div>
        </form>
    </div>
</div><!-- /.page-content -->
@endsection
