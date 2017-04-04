{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Tên nhà SX</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Tên nhà SX" value="{{ old('name', $manufacturer->name) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Mã nhà SX</label>
    <div class="col-sm-6">
        @if ($manufacturer->id)
        <p class="form-control-static"><strong>{{ $manufacturer->code }}</strong></p>
        @else
        <input type="text" class="form-control" name="code" placeholder="Mã nhà SX" value="{{ old('code', $manufacturer->code) }}">
        @endif
        <span class="help-block">
            Dùng để sinh SKU, để trống để sinh tự động.
        </span>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Trang chủ</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="homepage" placeholder="Trang chủ" value="{{ old('homepage', $manufacturer->homepage) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Kích hoạt</label>
    <div class="col-sm-6">
        <label>
            <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6"{{ old('status', !! $manufacturer->status) ? ' checked=checked' : '' }}>
            <span class="lbl"></span>
        </label>
    </div>
</div>

<div class="clearfix form-actions">
    <div class="col-md-offset-3 col-md-9">
        <button type="submit" class="btn btn-success">
            <i class="ace-icon fa fa-save bigger-110"></i>Lưu
        </button>
    </div>
</div>
