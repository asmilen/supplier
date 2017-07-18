{!! csrf_field() !!}

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Name</label>
    <div class="col-sm-6">
        <input type="text" class="form-control" name="name" placeholder="Name" value="{{ old('name', $user->name) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Email</label>
    <div class="col-sm-6">
        <input type="email" class="form-control" name="email" placeholder="Email" value="{{ old('email', $user->email) }}">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Role(s)</label>
    <div class="col-sm-6">
        <select name="roles[]" class="chosen-select form-control tag-input-style" data-placeholder="Choose Roles..." multiple>
            @foreach ($rolesList as $role)
            <option value="{{ $role->id }}"{{ in_array($role->id, old('roles', $user->getRolesList())) ? ' selected=selected' : '' }}>{{ $role->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Active</label>
    <div class="col-sm-6">
        <label>
            <input type="checkbox" name="active" value="1" class="ace ace-switch ace-switch-6"{{ old('active', $user->isActive()) ? ' checked=checked' : '' }}>
            <span class="lbl"></span>
        </label>
    </div>
</div>

<hr>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Password</label>
    <div class="col-sm-6">
        <input type="password" class="form-control" name="password" placeholder="Password">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Confirm Password</label>
    <div class="col-sm-6">
        <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password">
    </div>
</div>

<hr>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Cấp quản lý</label>
    <div class="col-sm-6">
        <select name="level" class="form-control chosen-select" id="level" data-placeholder="Chọn cấp quản lý">
            @foreach( config('teko.manager_levels') as $key => $value)
                <option value="{{$key}}" {{ ($supportedProvince && in_array($key,$supportedProvince->pluck('level')->toArray())) ? 'selected' : '' }}>{{$value}}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right">Thông tin quản lý</label>
    <div class="col-sm-6">
        <select id=area name="areas[]" class="chosen-select form-control tag-input-style" data-placeholder="Chọn Thông tin quản lý" multiple>
            @foreach( config('teko.regions') as $key => $value)
                <option value="{{$key}}" {{ ($supportedProvince && in_array($key,$supportedProvince->pluck('region_id')->toArray())) ? 'selected' : '' }} class="1">{{$value}}</option>
            @endforeach
            @foreach( \App\Models\Province::all() as $key => $value)
                <option value="{{$value->id}}" {{ ($supportedProvince && in_array($value->id,$supportedProvince->pluck('province_id')->toArray())) ? 'selected' : '' }} class="2">{{$value->name}}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="clearfix form-actions">
    <div class="col-md-offset-3 col-md-9">
        <button type="submit" class="btn btn-success">
            <i class="ace-icon fa fa-save bigger-110"></i>Save
        </button>
    </div>
</div>
