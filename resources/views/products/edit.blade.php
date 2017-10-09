@extends('layouts.app')

@section('inline_scripts')
<script>
    var PRODUCT_ID = {{ $product->id }};
</script>
@endsection

@section('content')
<div class="page-content" ng-controller="ProductEditController">
    <div class="page-header">
        <h1>
            Sản phẩm
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                {{ $product->name }}
            </small>
        </h1>
    </div><!-- /.page-header -->

    <div class="row">
        <div class="col-xs-6">
        </div>
        <div class="col-xs-6">
            @if (Sentinel::getUser()->hasAccess('products.index'))
            <p class="pull-right">
                <a class="btn btn-primary" href="{{ route('products.index') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </p>
            @endif
        </div>
    </div>

    <div class="row" ng-if="productIsLoaded">
        <div class="col-xs-12">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a data-toggle="tab" href="#tab-general">Thông tin chung</a>
                </li>

                <li>
                    <a data-toggle="tab" href="#tab-attributes">Quản lý Thuộc tính</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="tab-general" class="tab-pane fade in active">
                    @include('products._edit-general')
                </div>

                <div id="tab-attributes" class="tab-pane fade">
                    @include('products._edit-attributes')
                </div>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
