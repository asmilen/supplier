{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Danh mục</label>
    <div class="col-sm-6">
        <select name="category_id" class="form-control">
            <option value="">--Chọn Danh mục--</option>
            @foreach ($categoriesList as $id => $name)
            <option value="{{ $id }}"{{ $id == $product->category_id ? ' selected=selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Nhà SX</label>
    <div class="col-sm-6">
        <select name="manufacturer_id" class="form-control">
            <option value="">--Chọn Nhà SX--</option>
            @foreach ($manufacturersList as $id => $name)
            <option value="{{ $id }}"{{ $id == $product->manufacturer_id ? ' selected=selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Tên sản phẩm</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Tên sản phẩm" value="{{ old('name', $product->name) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Mã sản phẩm</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="code" placeholder="Mã sản phẩm" value="{{ old('code', $product->code) }}">
        <span class="help-block">
            Dùng để sinh SKU.
        </span>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">URL</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="source_url" placeholder="URL" value="{{ old('source_url', $product->source_url) }}">
        <span class="help-block">
            URL nguồn sản phẩm.
        </span>
    </div>
</div>

@if (! empty($product->old_sku))
<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Mã SKU cũ trên kho</label>
    <div class="col-sm-6">
        <p class="form-control-static"><strong>{{ $product->old_sku }}</strong></p>
    </div>
</div>
@endif

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Mã SKU</label>
    <div class="col-sm-6">
        <p class="form-control-static"><strong>{{ $product->sku or 'Chưa có mã SKU' }}</strong></p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Kích hoạt</label>
    <div class="col-sm-6">
        <label>
            <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6"{{ old('status', !! $product->status) ? ' checked=checked' : '' }}>
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
