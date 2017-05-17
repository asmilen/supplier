{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Tên nhà cung cấp</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Tên nhà cung cấp"
               value="{{ old('name', $supplier->name) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Mã nhà cung cấp</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="code" placeholder="Mã nhà cung cấp"
               value="{{ old('code', $supplier->code) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Phone</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="phone" placeholder="Phone..."
               value="{{ old('phone', $supplier->phone) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Fax</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="fax" placeholder="Fax ..."
               value="{{ old('fax', $supplier->fax) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Email</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="email" placeholder="email ..."
               value="{{ old('fax', $supplier->email) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Website</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="website" placeholder="website ..."
               value="{{ old('website', $supplier->website) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Tax Number</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="tax_number" placeholder="Tax Number ..."
               value="{{ old('tax_number', $supplier->tax_number) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Type</label>
    <div class="col-sm-6">
        <select name="type" class="form-control">
            <option value="">--Chọn Loại hóa đơn--</option>
            <option value="0" {{ 0 == $supplier->type ? ' selected=selected' : '' }}>Hóa đơn Trực tiếp</option>
            <option value="1" {{ 1 == $supplier->type ? ' selected=selected' : '' }}>Hóa đơn Gián tiếp</option>
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Kích hoạt</label>
    <div class="col-sm-6">
        <label>
            <input type="checkbox" name="status" value="1"
                   class="ace ace-switch ace-switch-6"{{ old('status', !! $supplier->status) ? ' checked=checked' : '' }}>
            <span class="lbl"></span>
        </label>
    </div>
</div>

<div class="clearfix"></div>
<h3 class="text-center"> Thông tin địa chỉ và tài khoản ngân hàng </h3>


<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Chọn Tỉnh</label>
    <div class="col-sm-6">
        <select name="province_id" id = "province_id" class="form-control">
            <option value="">--Chọn Tỉnh--</option>
            @foreach ($provincesList as $id => $name)
                <option value="{{ $id }}" {{ $id == $address->province_id ? ' selected=selected' : '' }}>{{  $name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Chọn Huyện</label>
    <div class="col-sm-6">
        <select name="district_id" id="district_id" class="form-control">
            <option value="">--Chọn Huyện--</option>
            @if(isset($distristList))
                @foreach($distristList as $district)
                    <option value="{{ $district->district_id }}" {{ $district->district_id == $address->district_id ? ' selected=selected' : '' }}>{{  $district->name }}</option>
                @endforeach
            @endif
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Địa chỉ</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="address" placeholder="Địa chỉ nhà cung cấp"
               value="{{ old('address', $address->address) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Mã địa chỉ Code</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="addressCode" id="addressCode" placeholder="Mã địa chỉ nhà cung cấp"
               value="{{ old('addressCode', $address->addressCode) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Contact Name</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="contact_name" placeholder="Contact Name ..."
               value="{{ old('contact_name', $address->contact_name) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Contact Mobile</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="contact_mobile" placeholder="Contact Mobile ..."
               value="{{ old('contact_mobile', $address->contact_mobile) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Contact Phone</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="contact_phone" placeholder="Contact Phone ..."
               value="{{ old('contact_phone', $address->contact_phone) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Contact Email</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="contact_email" placeholder="Contact Email ..."
               value="{{ old('contact_email', $address->contact_email) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Bank Account</label>
    <div class="col-sm-6">

        <input type="text" class="form-control" name="bank_account" placeholder="Bank Account ..." value="{{ old('bank_account', $supplier->supplier_bank['bank_account']) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Bank Account Name</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="bank_account_name" placeholder="Bank Account Name ..." value="{{ old('bank_account_name', $supplier->supplier_bank['bank_account_name']) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Bank Name</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="bank_name" placeholder="Bank Name ..." value="{{ old('bank_name', $supplier->supplier_bank['bank_name']) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Bank Code</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="bank_code" placeholder="Bank Code ..." value="{{ old('bank_code', $supplier->supplier_bank['bank_code']) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Bank Branch</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="bank_branch" placeholder="Bank Branch ..." value="{{ old('bank_branch', $supplier->supplier_bank['bank_branch']) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Bank Province</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="bank_province" placeholder="Bank Province ..." value="{{ old('bank_province', $supplier->supplier_bank['bank_province']) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Is_Default</label>
    <div class="col-sm-6">
        <label>
            <input type="checkbox" name="is_default" value="1"
                   class="ace ace-switch ace-switch-6"{{ old('is_default', !! $address->is_default) ? ' checked=checked' : '' }}>
            <span class="lbl"></span>
        </label>
    </div>
</div>

<div class="clearfix">
    <div class="col-md-offset-3 col-md-9">
        <button type="submit" class="btn btn-success">
            <i class="ace-icon fa fa-save bigger-110"></i>Lưu
        </button>
    </div>
</div>

