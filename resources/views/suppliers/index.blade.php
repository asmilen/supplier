@extends('layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">

    <link rel="stylesheet" href="https://editor.datatables.net/extensions/Editor/css/editor.dataTables.min.css">
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
            <a href="{{ route('suppliers.index') }}">Sản phẩm</a>
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
                        <th>Danh mục</th>
                        <th>Nhà sản xuất</th>
                        <th>SKU</th>
                        <th>Tên</th>
                        <th>Giá nhập</th>
                        <th>GTGT</th>
                        <th>Giá Saler</th>
                        <th>Giá Tekshop</th>
                        <th>Trạng thái </th>
                        <th>Khu Vuc</th>
                        <th>Tình trạng</th>
                        <th>Ngày cập nhật</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/dataTables.editor.min.js"></script>
@endsection

@section('inline_scripts')
<script>
$(function () {
    var editor = new $.fn.dataTable.Editor( {
        table: "#dataTables-products",
        idSrc:  'id',
        ajax: "{!! route('suppliers.datatables-edit') !!}",
        fields: [ {
            name: "status",
            type: "select",
            options: [
                { 'label': "Chờ duyệt", 'value': "Chờ duyệt" },
                { 'label': "Câp nhật", 'value': "Câp nhật" },
                { 'label': "Đã đăng", 'value': "Đã đăng" },
                { 'label': "Yêu cầu đăng", 'value': "Yêu cầu đăng" }
            ]
        }
        ]
    } );

    // Activate an inline edit on click of a table cell
    $('#dataTables-products').on( 'click', 'tbody td:nth-child(9)', function (e) {
        editor.inline( this, {
            buttons: {
                label: 'Save', fn: function () {
                    this.submit();
//                    datatable.draw();
                    window.location.reload();
                }
            }
        }
        );
    } );
    var datatable = $("#dataTables-products").DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        pageLength: 50,
        "bSort" : false,
        ajax: {
            url: '{!! route('suppliers.datatables') !!}',
            data: function (d) {
                //
            }
        },
        columns: [
            {data: 'cat_name', name: 'cat_name'},
            {data: 'manufacturer_name', name: 'manufacturer_name'},
            {data: 'sku', name: 'sku'},
            {data: 'product_name', name: 'product_name'},
            {data: 'import_price', name: 'import_price'},
            {data: 'vat', name: 'vat'},
            {data: 'saler_price', name: 'saler_price'},
            {data: 'recommend_price', name: 'recommend_price'},
            {data: 'status',name: 'status'},
            {data: 'region',name: 'region'},
            {data: 'status_product',name: 'status_product'},
            {data: 'updated_at',name: 'updated_at'},
            {data: 'action', name: 'action', searchable: false}
        ],
        select: {
            style:    'os',
            blurable: true
        },

    });

    datatable.on('key-focus', function (e, datatable, cell) {
        editor.inline(cell.index(), {
            onBlur: 'submit'
        });
    });


    @include('scripts.click-datatable-delete-button')
});
</script>
@endsection
