@extends('layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.css">
@endsection
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
                <a href="{{ route('suppliers.getList') }}">Nhà cung cấp</a>
            </li>
            <li class="active">Tạo mới</li>
        </ul><!-- /.breadcrumb -->
        <!-- /section:basics/content.searchbox -->
    </div>
    <!-- /section:basics/content.breadcrumbs -->

    <div class="page-content">
        <div class="page-header">
            <h1>
                Nhà cung cấp
                <small>
                    <i class="ace-icon fa fa-angle-double-right"></i>
                    Tạo mới
                </small>
                <a class="btn btn-primary pull-right" href="{{ route('suppliers.getList') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </h1>
        </div><!-- /.page-header -->
        <div class="row">
            <div class="col-xs-12">
                @include('common.errors')

                <form class="form-horizontal" role="form" method="POST" action="{{ route('suppliers.store') }}">
                    @include('suppliers._form')
                </form>
            </div>
        </div>
    </div><!-- /.page-content -->
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.min.js"></script>
@endsection
@section('inline_scripts')
    <script type="application/javascript">
        $( document ).ready(function() {
            $('#province_id').on('change', '', function (e) {
                loadDistrictsByProvince(this.value);
                loadAddressCode(this.value);
            });
            $(".provinces").select2({
                placeholder: "-- Chọn tỉnh --",
                allowClear: true,
                width:'100%',
            });
            $(".districts").select2({
                placeholder: "-- Chọn huyện --",
                allowClear: true,
                width:'100%',
            });
        });
        
        function loadDistrictsByProvince(provinceId) {
            $.ajax({
                url: "/provinces/" + provinceId + "/districts",
                success: function(districts) {
                    $("#district_id").html('');
                    $.each(districts, function(key, district) {
                        $("#district_id").append('<option value="' + district.district_id + '">' + district.name + '</option>')
                    });

                    $(".districts").select2({
                        allowClear: true,
                        width:'100%',
                    });
                },
                error: function() {

                }
            });
        }

        function loadAddressCode(provinceId) {
            $.ajax({
                url: "/provinces/" + provinceId + "/addressCode",
                success: function(addressCode) {
                    $("#addressCode").val(addressCode)
                },
                error: function() {

                }
            });
        }
    </script>
@endsection

