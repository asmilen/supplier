{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Name</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Name" value="{{ old('name', $role->name) }}">
    </div>
</div>

<div class="clearfix form-actions">
    <div class="col-md-offset-3 col-md-9">
        <button type="submit" class="btn btn-success">
            <i class="ace-icon fa fa-save bigger-110"></i>Save
        </button>
    </div>
</div>