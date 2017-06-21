@extends('layouts.app')
@section('inline_styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.css">
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
                <a href="{{ route('combo.index') }}">Sản phẩm Combo </a>
            </li>
            <li class="active">Thay đổi</li>
        </ul><!-- /.breadcrumb -->
        <!-- /section:basics/content.searchbox -->
    </div>
    <!-- /section:basics/content.breadcrumbs -->

    <div class="page-content">
        <div class="page-header">
            <h1>
                Nhóm sản phẩm
                <small>
                    <i class="ace-icon fa fa-angle-double-right"></i>
                    Thay đổi
                </small>
                <a class="btn btn-primary pull-right" href="{{ route('combo.index') }}">
                    <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                    <span class="hidden-xs">Danh sách</span>
                </a>
            </h1>
        </div><!-- /.page-header -->
        <div class="row">
            <div class="col-xs-12">
                @include('common.errors')

                <form class="form-horizontal" role="form" method="POST" action="{{ route('combo.update', $combo->id) }}">
                    {!! method_field('PUT') !!}
                    {!! csrf_field() !!}

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Tên combo</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="name" placeholder="Tên danh mục ...." value="{{ old('name', $combo->name) }}" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Giá Combo</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="price" placeholder="Giá combo ...." value="{{ old('price', $combo->price) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right">Kích hoạt</label>
                        <div class="col-sm-6">
                            <label>
                                <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6"{{ old('status', !! $combo->status) ? ' checked=checked' : '' }}>
                                <span class="lbl"></span>
                            </label>
                        </div>
                    </div>

                    <label class="control-label no-padding-right">Sản phẩm thuộc combo</label>
                    <br>
                    <div>
                        <table class="table hoverTable" id="products-table">
                            <thead>
                            <th>ID</th>
                            <th>Tên sản phẩm</th>
                            <th>Sku</th>
                            <th>Số lượng</th>
                            <th>Thao tác</th>
                            </thead>
                            <tbody id = "bundleProducts">
                            @if($productsCombo)
                                @foreach($productsCombo as $key => $productCombo)
                                    <tr>
                                        <input type ="hidden" name= "productIds[]" value="{{ $productCombo->id }}"/>
                                        <td class="id">{{ $productCombo->id }}</td>
                                        <td class="name">{{ $productCombo->name }}</td>
                                        <td class="sku">{{ $productCombo->sku }}</td>
                                        <td><input name="quantity[]" type="number" min = 0 value="{{ $productCombo->pivot->quantity }}"/></td>
                                        <td><a class="deleteProduct"  data-productId ="{{ $productCombo->id }}" href=""><i class="fa fa-trash-o" aria-hidden="true"></i></a></td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                            <tfoot >
                            </tfoot>
                        </table>
                    </div>

                    <div class="clearfix form-actions">
                        <div class="col-md-offset-3 col-md-9">
                            <button type="submit" class="btn btn-success">
                                <i class="ace-icon fa fa-save bigger-110"></i>Lưu
                            </button>

                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModalProduct">
                                <i class="ace-icon fa fa-save bigger-110"></i>Thêm sản phẩm
                            </button>

                            <!-- Modal Product to Connect -->
                            <div class="modal fade" id="myModalProduct" role="dialog">
                                <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Chọn sản phẩm cho nhóm sản phẩm</h4>
                                        </div>
                                        <div class="modal-body">
                                            <table id="tableproducts" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Tên</th>
                                                    <th>SKU</th>
                                                    <th>Trạng thái</th>
                                                    <th>Chọn </th>
                                                    <th>Số Lượng</th>
                                                </tr>
                                                </thead>
                                                <tbody id="productsRegion">

                                                </tbody>
                                            </table>
                                            <br>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label no-padding-left"></label>
                                                <button type="button" class="btn btn-success" id = "btnChooseProduct">
                                                    <i class="ace-icon fa fa-save bigger-110"></i>Chọn sản phẩm
                                                </button>
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- /.page-content -->
@endsection
@section('scripts')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
@endsection

@section('inline_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            var productsTable = '';
            productsTable = $("#products-table").DataTable({
                autoWidth: false,
                searching: true,
                'columns': [
                    { 'searchable': false },
                    { 'searchable': true },
                    { 'searchable': true },
                    null,   // product code
                    null,   // description
                ]
            });

            var productIds = {{ $productIds }};
            var table = $("#tableproducts").DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{!! route('products.getProductInCombo') !!}",
                    data: function (d) {
                        d.productIds = productIds
                    },
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name', className:'productName'},
                    {data: 'sku', name: 'sku', className:'productSku'},
                    {data: 'status', name: 'status'},
                    {data: 'check', name: 'check', orderable: false, searchable: false},
                    {data: 'quantity',name: 'quantity', orderable: false, searchable: false},
                ],
            });

            $('.deleteProduct').click( function (e) {
                e.preventDefault();
                var comboId = {{ $combo->id }};
                var productId = $(this).attr('data-productId');
                var r = confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi nhóm sản phẩm!");
                if (r == true) {
                    destroyProduct(productId,comboId);
                } else {
                    return false;
                }
            });

            $(document).on('click', '#btnChooseProduct', function(e) {
                productsTable.destroy();
                var productNames = [];
                var productIds = [];
                var productSkus = [];
                var productQtys = [];
                var rowcollection =  table.$(".checkbox:checked", {"page": "all"});
                for(var i = 0; i < rowcollection.length; i++)
                {
                    productNames.push($(rowcollection[i]).closest('tr').find('.productName').text());
                    productIds.push(parseInt($(rowcollection[i]).val()));
                    productSkus.push($(rowcollection[i]).closest('tr').find('.productSku').text());
                    productQtys.push($(rowcollection[i]).closest('tr').find('.qty').val());
                    if($(rowcollection[i]).closest('tr').find('.radio').is(':checked')) {
                        productDefault = $(rowcollection[i]).closest('tr').find('.radio').val();
                    }
                    $(rowcollection[i]).closest('tr').remove();
                }

                for(var i = 0; i < productNames.length; i++) {

                    $("#bundleProducts").append('<tr>' +
                        '<input type ="hidden" name= "productIds[]" value="' +productIds[i] + '"/>' +
                        '<td class="id">' + productIds[i] + '</td>'   +
                        '<td class="name">' + productNames[i] + '</td>' +
                        '<td class="sku">' + productSkus[i] + '</td>'  +
                        '<td><input type = "number" name = "quantity[]" min = 0 value="' + productQtys[i] + '"/></td>'  +
                        '<td><a class="deleteProduct" href=""><i class="fa fa-trash-o" aria-hidden="true"></i></a></td>'  +
                        + '</tr>');
                }
                $("#myModalProduct").hide();
                $("body").removeClass("modal-open");
                productsTable = $("#products-table").DataTable({
                    autoWidth: false,
                    searching: true,
                    'columns': [
                        { 'searchable': false },
                        { 'searchable': true },
                        { 'searchable': true },
                        null,   // product code
                        null,   // description
                    ]
                });
                var dataRows = productsTable.rows().data();
                var productIds = [];
                for (var i = 0; i< dataRows.length; i++) {
                    productIds.push(parseInt(dataRows[i][0]));
                }
                loadProduct(productIds);
            });

            $(document).on('click', '.deleteProduct', function(e) {
                e.preventDefault();
                productsTable.row( $(this).parents('tr') ).remove().draw();
                var dataRows = productsTable.rows().data();
                var productIds = [];
                for (var i = 0; i< dataRows.length; i++) {
                    productIds.push(parseInt(dataRows[i][0]));
                }
                loadProduct(productIds);
            });

            function loadProduct(productIds) {
                productIds = typeof productIds !== 'undefined' ? productIds : [];
                table.destroy();
                table = $("#tableproducts").DataTable({
                    autoWidth: false,
                    processing: true,
                    serverSide: true,
                    pageLength: 10,
                    ajax: {
                        url: "{!! route('products.getProductInCombo') !!}",
                        data: function (d) {
                            d.productIds = productIds
                        },
                    },
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'name', name: 'name', searchable: true, className:'productName'},
                        {data: 'sku', name: 'sku', searchable: true, className:'productSku'},
                        {data: 'status', name: 'status'},
                        {data: 'check', name: 'check', orderable: false, searchable: false},
                        {data: 'quantity',name: 'quantity', orderable: false, searchable: false},
                    ],
                });
            }

            function destroyProduct(productId,comboId) {
                $.ajax({
                    url: "{{ url('combo/destroyProduct') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        productId : productId,
                        comboId : comboId,
                    },
                    dataType: "json",
                    success: function(response){
                        productsTable.row( $(this).parents('tr') ).remove().draw();
                        var dataRows = productsTable.rows().data();
                        var productIds = [];
                        for (var i = 0; i< dataRows.length; i++) {
                            productIds.push(parseInt(dataRows[i][0]));
                        }
                        loadProduct(productIds);
                    }
                });
            }
        });

    </script>
@endsection
