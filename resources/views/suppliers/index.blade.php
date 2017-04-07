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
                        <th>Tên</th>
                        <th>SKU</th>
                        <th>Danh mục</th>
                        <th>Nhà SX</th>
                        <th>Mã</th>
                        <th>URL</th>
                        <th>Trạng thái</th>

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
        ajax: {
            type: "POST",
            url: '{!! route('suppliers.datatables_edit') !!}',
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        },
        table: "#dataTables-products",
        idSrc:  'id',
        fields: [ {
            label: "Tên:",
            name: "name"
        }, {
            label: "SKU:",
            name: "sku"
        }, {
            label: "Danh mục:",
            name: "category_id"
        }, {
            label: "Nhà SX:",
            name: "manufacturer_id"
        }, {
            label: "Mã:",
            name: "code"
        }, {
            label: "URL:",
            name: "source_url",

        }, {
            label: "Trạng thái:",
            name: "salary"
        }
        ]
    } );

    // Activate an inline edit on click of a table cell
    $('#dataTables-products').on( 'click', 'tbody td', function (e) {
        editor.inline( this, {
            buttons: { label: '&gt;', fn: function () { this.submit(); } }
        } );
    } );
    var datatable = $("#dataTables-products").DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: {
            url: '{!! route('products.datatables') !!}',
            data: function (d) {
                //
            }
        },
        columns: [
            {data: 'name', name: 'name'},
            {data: 'sku', name: 'sku'},
            {data: 'category_id', name: 'category_id'},
            {data: 'manufacturer_id', name: 'manufacturer_id'},
            {data: 'code', name: 'code'},
            {data: 'source_url', name: 'source_url'},
            {data: 'status', name: 'status'},

        ],
        select: {
            style:    'os',
            selector: 'td:first-child'
        },

    });

    @include('scripts.click-datatable-delete-button')
});
</script>
@endsection
