@extends('layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">

    <link rel="stylesheet" href="https://editor.datatables.net/extensions/Editor/css/editor.dataTables.min.css">
    <style>
        .my-confirm-class {
            padding: 3px 6px;
            font-size: 12px;
            color: white;
            text-align: center;
            vertical-align: middle;
            border-radius: 4px;
            background-color: #337ab7;
            text-decoration: none;
        }
        .my-cancel-class {
            padding: 3px 4px;
            font-size: 12px;
            color: white;
            text-align: center;
            vertical-align: middle;
            border-radius: 4px;
            background-color: #a94442;
            text-decoration: none;
        }

    </style>
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
    <script src="/vendor/ace/assets/js/dataTables/dataTables.cellEdit.js"></script>
@endsection

@section('inline_scripts')
<script>
$(function () {

    var datatable = $("#dataTables-products").DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        pageLength: 50,
        "bSort" : false,
        "bDestroy": true,
        ajax: {
            url: '{!! route('suppliers.datatables') !!}',
            data: function (d) {
                //
            }
        },
        columns: [
            {data: 'cat_name', name: 'cat_name',"width": "10%"},
            {data: 'manufacturer_name', name: 'manufacturer_name',"width": "5%"},
            {data: 'sku', name: 'sku',"width": "10%"},
            {data: 'product_name', name: 'product_name',"width": "15%"},
            {data: 'import_price', name: 'import_price',"width": "5%"},
            {data: 'vat', name: 'vat',"width": "5%"},
            {data: 'saler_price', name: 'saler_price',"width": "5%"},
            {data: 'recommend_price', name: 'recommend_price',"width": "5%"},
            {data: 'status',name: 'status',"width": "20%"},
            {data: 'region',name: 'region',"width": "5%"},
            {data: 'status_product',name: 'status_product',"width": "10%"},
            {data: 'updated_at',name: 'updated_at',"width": "5%"},
            {data: 'action', name: 'action', searchable: false,"width": "5%"}
        ],
        select: {
            style:    'os',
            blurable: true
        },
    });

    datatable.MakeCellsEditable({
        "onUpdate": myCallbackFunction,
        "inputCss":'my-input-class',
        "idSrc":  'id',
        "columns": [8],
        "allowNulls": {
            "columns": [1],
            "errorClass": 'error'
        },
        "confirmationButton": { // could also be true
            "confirmCss": 'my-confirm-class',
            "cancelCss": 'my-cancel-class'
        },
        "inputTypes": [
            {
                "column":8,
                "type": "list",
                "options":[
                    { "value": "Chờ duyệt", "display": "Chờ duyệt" },
                    { "value": "Câp nhật", "display": "Câp nhật" },
                    { "value": "Đã đăng", "display": "Đã đăng" },
                    { "value": "Yêu cầu đăng", "display": "Yêu cầu đăng" }
                ]
            }
        ]
    });


    function myCallbackFunction (updatedCell, updatedRow, oldValue) {
        var data = updatedRow.data();
        var id = data.id;
        var status = data.status;
        $.ajax({
            url: "{!! route('suppliers.datatables-edit') !!}",
            type: "POST",
            data: {
                id : id,
                status: status
            },
            dataType: "json"
        });
    }

    @include('scripts.click-datatable-delete-button')
});
</script>
@endsection
