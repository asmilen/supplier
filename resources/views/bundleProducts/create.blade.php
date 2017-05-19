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
                <a href="{{ route('bundleProducts.index') }}">Sản phẩm </a>
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
                    Sản phẩm
                </small>
                <a class="btn btn-primary pull-right" href="{{ route('bundleProducts.index') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </h1>
        </div><!-- /.page-header -->
        <div class="row">
            <div class="col-xs-12">
                @include('common.errors')

                <form class="form-horizontal" role="form" method="POST" action="{{ route('bundleProducts.store', $bundleCategory->id) }}">
                    {!! method_field('PUT') !!}
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Nhóm sản phẩm</label>
                        <div class="col-sm-6">
                            <select name="id_bundle" class="form-control" readonly="true">
                                <option value="{{ $bundleCategory->bundle->id }}">{{ $bundleCategory->bundle->name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Tên danh mục của nhóm sản phẩm</label>
                        <div class="col-sm-6">
                            <select name="id_bundleCategory" class="form-control" readonly="true">
                                <option value="{{ $bundleCategory->id }}">{{ $bundleCategory->name }}</option>
                            </select>
                        </div>
                    </div>

                    @foreach ($categories as $category)
                        <?php  $products = $category->products;?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Chọn sản phẩm cho danh mục " {{ $category->name }} " </label>
                            <div class="col-sm-6">
                                <select name="id_product[]" class="form-control">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Số lượng</label>
                        <div class="col-sm-6">
                           <input type="integer" name="quantity" value="{{ old('quantity', $bundleProduct->quantity) }}"  class="form-control"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Mặc định</label>
                        <div class="col-sm-6">
                            <label>
                                <input type="checkbox" name="is_default" value="1"
                                       class="ace ace-switch ace-switch-6"{{ old('is_default', !! $bundleProduct->is_default) ? ' checked=checked' : '' }}>
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
