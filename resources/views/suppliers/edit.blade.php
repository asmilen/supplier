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
                <a href="{{ route('products.index') }}">Nhà cung cấp</a>
            </li>
            <li class="active">Thay đổi</li>
        </ul><!-- /.breadcrumb -->
        <!-- /section:basics/content.searchbox -->
    </div>
    <!-- /section:basics/content.breadcrumbs -->

    <div class="page-content">
        <div class="page-header">
            <h1>
                Sản phẩm
                <small>
                    <i class="ace-icon fa fa-angle-double-right"></i>
                    Thay đổi
                </small>
                <a class="btn btn-primary pull-right" href="{{ route('suppliers.index') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </h1>
        </div><!-- /.page-header -->
        <div class="row">
            <div class="col-xs-12">
                @include('common.errors')

                <form class="form-horizontal" role="form" method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                    {!! method_field('PUT') !!}

                    @include('suppliers._form')
                </form>
            </div>
        </div>
    </div><!-- /.page-content -->
@endsection
@section('inline_scripts')
    <script type="application/javascript">
        $( document ).ready(function() {
            $('#province_id').on('change', '', function (e) {
                loadDistrictsByProvince(this.value);
            });
        });

        function loadDistrictsByProvince(provinceId) {
            $("#district_id").html();
            $.ajax({
                url: "/provinces/" + provinceId + "/districts",
                success: function(districts) {
                    $.each(districts, function(key, district) {
                        $("#district_id").append('<option value="' + district.district_id + '">' + district.name + '</option>')
                    })
                },
                error: function() {

                }
            });
        }
    </script>
@endsection
