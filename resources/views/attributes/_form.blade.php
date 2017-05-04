{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Tên thuộc tính</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Tên thuộc tính" value="{{ old('name', $attribute->name) }}">
    </div>
</div>

<div class="clearfix form-actions">
    <div class="col-md-offset-3 col-md-9">
        <button type="submit" class="btn btn-success">
            <i class="ace-icon fa fa-save bigger-110"></i>Lưu
        </button>
    </div>
</div>
