{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Nhóm sản phẩm</label>
    <div class="col-sm-6">
        <select name="category_id" class="form-control">
            <option value="">--Chọn nhóm sản phẩm--</option>
            @foreach ($bundlesList as $id => $name)
                <option value="{{ $id }}"{{ $id == $bundleCategory->id_bundle ? ' selected=selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Danh mục từ hệ thống</label>
    <div class="col-sm-6">
        <select name="category_id[]" class="form-control">
            <option value="">--Chọn danh mục từ hệ thống--</option>
            @foreach ($categoriesList as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <a class="">
            <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
        </a>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Tên danh mục theo nhóm sản phẩm</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Tên danh mục ...." value="{{ old('name', $bundleCategory->name) }}">
    </div>
</div>


<div class="clearfix form-actions">
    <div class="col-md-offset-3 col-md-9">
        <button type="submit" class="btn btn-success">
            <i class="ace-icon fa fa-save bigger-110"></i>Lưu
        </button>
    </div>
</div>
