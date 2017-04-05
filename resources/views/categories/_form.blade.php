{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Tên danh mục</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Tên danh mục" value="{{ old('name', $category->name) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Mã danh mục</label>
    <div class="col-sm-6">
        @if ($category->id)
        <p class="form-control-static"><strong>{{ $category->code }}</strong></p>
        @else
        <input type="text" class="form-control" name="code" placeholder="Mã danh mục" value="{{ old('code', $category->code) }}">
        <span class="help-block">
            Dài 3 kí tự bao gồm chữ cái và số. Dùng để sinh SKU.
        </span>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Kích hoạt</label>
    <div class="col-sm-6">
        <label>
            <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6"{{ old('status', !! $category->status) ? ' checked=checked' : '' }}>
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
