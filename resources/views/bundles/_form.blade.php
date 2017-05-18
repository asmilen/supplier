{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Tên nhóm sản phẩm</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Tên nhóm sản phẩm ...." value="{{ old('name', $bundle->name) }}">
    </div>
</div>

<div class="form-group">
<label class="col-sm-3 control-label no-padding-right">Giá</label>
<div class="col-sm-6">
    <input type="number" class="form-control" name="price" placeholder="Giá ... " value="{{ old('price', $bundle->price) }}">
</div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Miền</label>
    <div class="col-sm-6">
        <select name="region_id" class="form-control">
            <option value="1" <?php if($bundle->region_id == 1) echo 'selected' ?>>Miền Bắc</option>
            <option value="2" <?php if($bundle->region_id == 2) echo 'selected' ?>>Miền Trung</option>
            <option value="3" <?php if($bundle->region_id == 3) echo 'selected' ?>>Miền Nam</option>
        </select>
    </div>
</div>

<div class="clearfix form-actions">
    <div class="col-md-offset-3 col-md-9">
        <button type="submit" class="btn btn-success">
            <i class="ace-icon fa fa-save bigger-110"></i>Lưu
        </button>
    </div>
</div>
