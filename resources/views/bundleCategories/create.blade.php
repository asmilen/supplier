@extends('layouts.app')
@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.css">
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
            <a href="{{ route('bundles.index') }}">Danh mục theo nhóm sản phẩm</a>
        </li>
        <li class="active">Tạo mới</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Danh mục theo nhóm sản phẩm
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Tạo mới
            </small>
            <a class="btn btn-primary pull-right" href="{{ route('bundleCategories.index') }}">
                <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                <span class="hidden-xs">Danh sách</span>
            </a>
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            @include('common.errors')

            <form class="form-horizontal" role="form" method="POST" action="{{ route('bundleCategories.store') }}">
                {!! csrf_field() !!}

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Nhóm sản phẩm</label>
                    <div class="col-sm-6">
                        <select name="bundle_id" class="form-control">
                            <option value="">--Chọn nhóm sản phẩm--</option>
                            @foreach ($bundlesList as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="category">
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Danh mục từ hệ thống</label>
                        <div class="col-sm-6">
                            <select name="category_id[]" class="multiple" multiple="multiple">
                                @foreach ($categoriesList as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Tên danh mục theo nhóm sản phẩm</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="name" placeholder="Tên danh mục ...." value="{{ old('name', $bundleCategory->name) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Bắt buộc</label>
                    <div class="col-sm-6">
                        <label>
                            <input type="checkbox" name="isRequired" value="1"
                                   class="ace ace-switch ace-switch-6"{{ old('isRequired', !! $bundleCategory->isRequired) ? ' checked=checked' : '' }}>
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
@section('inline_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.min.js"></script>
<script type="application/javascript">
    $(document).ready(function() {
        $(".multiple").select2({
            width: '100%'
        });
    });


</script>
@endsection
