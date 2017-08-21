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
                Báo cáo
            </li>
            <li class="active">Giá nhập</li>
        </ul><!-- /.breadcrumb -->
        <!-- /section:basics/content.searchbox -->
    </div>
    <!-- /section:basics/content.breadcrumbs -->

    <div class="page-content">
        <div class="page-header">
            <h1>
                Báo cáo
                <small>
                    <i class="ace-icon fa fa-angle-double-right"></i>
                    Giá nhập
                </small>
            </h1>
        </div><!-- /.page-header -->

        <div class="row">
            <div class="col-xs-12">
                <div class="widget-box">
                    <div class="widget-header">
                        <h5 class="widget-title">Search</h5>
                    </div>

                    <div class="widget-body">
                        <div class="widget-main">
                            <form class="form-inline" id="search-form" action="">
                                <select class="form-control" name="region_id">
                                    <option value="">-- Miền --</option>
                                    @foreach (config('teko.regions') as $key => $region)
                                        <option value="{{ $key }}" {{(app('request')->input('region_id') == $key) ? 'selected' : ''}}>{{ $region }}</option>
                                    @endforeach
                                </select>
                                <select class="form-control" name="supplier_id">
                                    <option value="">-- Nhà cung cấp --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{(app('request')->input('supplier_id') == $supplier->id) ? 'selected' : ''}}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-purple btn-sm">
                                    <span class="ace-icon fa fa-search icon-on-right bigger-110"></span> Search
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 infobox-container">
                    <div class="infobox infobox-green">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-cube"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ $listProduct->total() }}</span>
                            <div class="infobox-content">Sản phẩm quá hạn</div>
                        </div>
                    </div>
                    <div class="infobox infobox-blue">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-calendar-times-o"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ $maxTime }} ngày</span>
                            <div class="infobox-content">Thời gian quá hạn nhập giá lâu nhất</div>
                        </div>
                    </div>
                    <div class="infobox infobox-red">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-calendar-times-o"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ number_format( $avgTime , 2 ) }} ngày</span>
                            <div class="infobox-content">Thời gian quá hạn nhập giá trung bình</div>
                        </div>
                    </div>
            </div>
        </div>

        <br>
        <br>

        <div class="row">
            <div class="col-xs-12">
                <table id="dataTables-product-suppliers" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                    <thead>
                        <tr>
                            <th>Nhà cung cấp</th>
                            <th>Tên sản phẩm</th>
                            <th>Cập nhật lần cuối</th>
                            <th>Thời gian hết hạn</th>
                            <th>Thời gian quá hạn</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($listProduct as $product)
                        <tr>
                            <td>{{$product->supplier_name}}</td>
                            <td>{{$product->product_name}}</td>
                            <td>{{$product->updated_at}}</td>
                            <td>{{$product->to_date}}</td>
                            <td>{{$product->out_dated_time}} ngày</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $listProduct->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('inline_scripts')
    <script>

    </script>
@endsection
