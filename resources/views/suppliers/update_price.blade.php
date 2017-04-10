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
                <a href="{{ route('products.index') }}">Sản phẩm</a>
            </li>
            <li class="active">Danh sách</li>
        </ul><!-- /.breadcrumb -->
        <!-- /section:basics/content.searchbox -->
    </div>
    <!-- /section:basics/content.breadcrumbs -->

    <div class="page-content">
        <div class="row">
            <div class="col-xs-4">
                <table id="dataTables-products" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                    <thead>
                    <tr>
                        <th>Mã sản phẩm</th>
                        <th>Tên sản phẩm</th>
                        <th></th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="col-xs-8">
                @include('common.errors')
                <form class="form-horizontal" role="form" id="product_form" action="{{ route('supplier.updatePrice') }}" method="POST" >
                    {!! csrf_field() !!}
                    <input type="hidden" name="product_id" id="product_id" />
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Tên sản phẩm</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="product_name" id="product_name" placeholder="Nhập tên sản phẩm" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Giá bán (có VAT)</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="import_price" placeholder="Nhập giá" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">VAT</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="vat" placeholder="Nhập VAT" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Tình trạng</label>
                        <div class="col-sm-6">
                            <select name="state" class="form-control">
                                <option value="0">Hết hàng</option>
                                <option value="1">Còn hàng</option>
                                <option value="2">Đặt hàng</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label no-padding-left"></label>
                        <button type="submit" class="btn btn-success">
                            <i class="ace-icon fa fa-save bigger-110"></i>Lưu thông tin
                        </button>
                        <a onclick="cancel()" class="btn btn-danger">
                            <i class="ace-icon fa fa-trash bigger-110"></i>Hủy
                        </a>
                    </div>
                </form>

                <table id="dataTables-products_suppliers" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                    <thead>
                    <tr>
                        <th>Loại</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá nhập</th>
                        <th>Cập nhật</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                    </thead>
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
        function cancel() {
            $('#product_id').val("");
            $('#product_name').val("");
           // $('#product_name').prop('disabled', false);
        }

        $(function () {
            var datatable = $("#dataTables-products").DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: '{!! route('products.datatables') !!}',
                    data: function (d) {
                        d.category_id = $('select[name=category_id]').val();
                        d.manufacturer_id = $('select[name=manufacturer_id]').val();
                        d.keyword = $('input[name=keyword]').val();
                        d.status = $('select[name=status]').val();
                    }
                },
                columns: [
                    {data: 'sku', name: 'sku'},
                    {data: 'name', name: 'name'},
                    {
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": '<a class="green" href="#"><i class="ace-icon fa fa-plus bigger-130"></i></a>'
                    },
                ]
            });

            $('#dataTables-products tbody').on( 'click', 'a', function () {
                var data = datatable.row( $(this).parents('tr') ).data();
                $('#product_id').val(data.id);
                $('#product_name').val(data.name);
                //$('#product_name').prop('disabled', true);
            } );



            var supplier_datatable = $("#dataTables-products_suppliers").DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: '{!! route('supplier.supplier_datatables') !!}',
                    data: function (d) {
                    }
                },
                columns: [
                    {data: 'category_name', name: 'category_name'},
                    {data: 'product_name', name: 'product_name'},
                    {data: 'import_price', name: 'import_price'},
                    {data: 'updated_at', name: 'updated_at'},
                    {data: 'status', name: 'status'},
                    {
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": '<a class="blue" href="#"><i class="ace-icon fa fa-pencil bigger-130"></i></a>'
                    },
                ]
            });

            $('#dataTables-products_suppliers tbody').on( 'click', 'a', function () {
                var data = datatable.row( $(this).parents('tr') ).data();
                $('#product_id').val(data.id);
                $('#product_name').val(data.name);
                //$('#product_name').prop('disabled', true);
            } );
        });
    </script>
@endsection
