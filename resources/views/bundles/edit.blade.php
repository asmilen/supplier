@extends('layouts.app')

@section('content')
<!-- #section:basics/content.breadcrumbs -->
<div class="breadcrumbs" id="breadcrumbs">
    <script type="text/javascript">
        try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
    </script>

    <ul class="breadcrumb">
        <li>
            <i class="ace-icon fa fa-home home-icon"></i>
            <a href="{{ url('/dashboard') }}">Dashboard</a>
        </li>
        <li>
            <a href="{{ route('manufacturers.index') }}">Nhóm sản phẩm </a>
        </li>
        <li class="active">Thay đổi</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Nhóm sản phẩm
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Thay đổi
            </small>
            <a class="btn btn-primary pull-right" href="{{ route('bundles.index') }}">
                <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                <span class="hidden-xs">Danh sách</span>
            </a>
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            @include('common.errors')

            <form class="form-horizontal" role="form" method="POST" action="{{ route('bundles.update', $bundle->id) }}">
                {!! method_field('PUT') !!}
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

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Label</label>
                    <div class="col-sm-6">
                        <select name="label" class="form-control">
                            @php $bundleLabels = config('teko.bundleLabels') @endphp
                            @foreach($bundleLabels as $key => $bundleLabel)
                                <option value="{{ $key }}" {{ $bundle->label == $key ? ' selected=selected' : '' }}>{{ $bundleLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Kích hoạt</label>
                    <div class="col-sm-6">
                        <label>
                            <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6"{{ old('status', !! $bundle->status) ? ' checked=checked' : '' }}>
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

            </form>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection

