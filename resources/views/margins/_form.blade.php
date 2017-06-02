{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Label (là giá trị "From")</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="label" placeholder="Label" value="{{ old('label', $margin->label) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Value</label>
    <div class="col-sm-6">
        <input type="number" min="0" class="form-control" name="value" placeholder="Value" value="{{ old('value', $margin->value) }}">
    </div>
</div>

<div class="clearfix form-actions">
    <div class="col-md-offset-3 col-md-9">
        <button type="submit" class="btn btn-success">
            <i class="ace-icon fa fa-save bigger-110"></i>Lưu
        </button>
    </div>
</div>
