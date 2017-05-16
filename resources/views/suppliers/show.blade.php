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
                <a href="{{ route('products.index') }}">Sản phẩm của nhà cung cấp</a>
            </li>
            <li class="active">Danh sách</li>
        </ul><!-- /.breadcrumb -->
        <!-- /section:basics/content.searchbox -->
    </div>
    <!-- /section:basics/content.breadcrumbs -->

    <div class="page-content">
        <div class="row">
            <div class="col-xs-12">
                <table id="dataTables-products" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                    <thead>
                        <tr>
                            <th>Tên</th>
                            <th>SKU</th>
                            <th>Danh mục</th>
                            <th>Nhà SX</th>
                            <th>Mã</th>
                            <th>URL</th>
                            <th>Tình trạng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->category_name }}</td>
                                <td>{{ $product->manufacture_name }}</td>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->source_url }}</td>
                                <td>
                                    @if($product->state == 0)
                                        {{ 'Hết hàng' }}
                                    @elseif($product->state == 1)
                                        {{ 'Còn hàng' }}
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /.page-content -->
@endsection

@section('scripts')
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
@endsection

@section('inline_scripts')
    <script>
        $(function () {
            var datatable = $("#dataTables-products").DataTable({
                searching: true,
            });
        });
    </script>
@endsection
