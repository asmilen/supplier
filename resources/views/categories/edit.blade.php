@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="page-header">
        <h1>
            Danh mục
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                {{ $category->name }}
            </small>
        </h1>
    </div><!-- /.page-header -->

    <div class="row">
        <div class="col-xs-6">
        </div>
        <div class="col-xs-6">
            @if (Sentinel::getUser()->hasAccess('categories.create'))
            <p class="pull-right">
                <a class="btn btn-primary" href="{{ route('categories.index') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </p>
            @endif
        </div>
    </div>

    <div class="row">
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
                    <p>Raw denim you probably haven't heard of them jean shorts Austin.</p>
                </div>

                <div id="tab-attributes" class="tab-pane fade">
                    <p>Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.</p>
                </div>
            </div>
            @include('common.errors')

            <form class="form-horizontal" role="form" method="POST" action="{{ route('categories.update', $category->id) }}">
                {!! method_field('PUT') !!}

                @include('categories._form')
            </form>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
