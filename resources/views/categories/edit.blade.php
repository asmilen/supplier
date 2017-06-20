@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="/vendor/ace/assets/css/bootstrap-duallistbox.css">
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
            <a href="{{ route('categories.index') }}">Danh mục</a>
        </li>
        <li class="active">Thay đổi</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content">
    <div class="page-header">
        <h1>
            Danh mục
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Thay đổi
            </small>
            <p class="pull-right">
                <a class="btn btn-primary" href="{{ route('categories.index') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </p>
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            @include('common.errors')

            <form class="form-horizontal" role="form" method="POST" action="{{ route('categories.update', $category->id) }}">
                {!! method_field('PUT') !!}

                @include('categories._form')
            </form>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection

@section('scripts')
<script src="/vendor/ace/assets/js/jquery.bootstrap-duallistbox.js"></script>
@endsection

@section('inline_scripts')
<script>
$(function () {
    var attributes = $('select[name="attributes[]"]').bootstrapDualListbox({infoTextFiltered: '<span class="label label-purple label-lg">Lọc</span>'});
    var attributesContainer = attributes.bootstrapDualListbox('getContainer');
    attributesContainer.find('.btn').addClass('btn-white btn-info btn-bold');
});
</script>
@endsection
