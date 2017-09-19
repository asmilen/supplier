{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Tên màu sắc</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Tên màu sắc" value="{{ old('name', $color->name) }}">
    </div>
</div>

<div class="form-group">
<label class="col-sm-3 control-label no-padding-right">Mã màu sắc</label>
<div class="col-sm-6">
    @if ($color->id)
        <p class="form-control-static"><strong>{{ $color->code }}</strong></p>
    @else
        <input type="text" class="form-control" name="code" placeholder="Mã màu" value="{{ old('code', $color->code) }}">
        <span class="help-block">
            Dài từ 3~6 kí tự bao gồm chữ cái và số. Dùng để sinh SKU, để trống để sinh tự động.
        </span>
    @endif
</div>
</div>

<div class="clearfix form-actions">
    <div class="col-md-offset-3 col-md-9">
        <button type="submit" class="btn btn-success">
            <i class="ace-icon fa fa-save bigger-110"></i>Lưu
        </button>
    </div>
</div>
