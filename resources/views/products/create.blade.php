@extends('layouts.app')

@section('inline_scripts')
@isset($category)
<script>
    var CATEGORY_ID = {{ $category->id }};
</script>
@endif
@endsection

@section('content')
<div class="page-content">
    <div class="page-header">
        <h1>
            Sản phẩm
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Tạo mới
                @isset($category)
                ({{ $category->name }})
                @endif
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

    <div class="row">
        <div class="col-xs-12">
            @isset($category)
                @include('products._create', compact('category'))
            @else
                @include('products._select-category')
            @endif
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
