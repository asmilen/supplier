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
                <table id="dataTables-products" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top updatePrice">
                    <thead>
                        <tr>
                            <th>Mã sản phẩm</th>
                            <th>Tên sản phẩm</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Mã sản phẩm</th>
                        <th>Tên sản phẩm</th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-xs-8">
                @include('common.errors')
                <form enctype="multipart/form-data" class="form-horizontal" role="form" id="product_form" action="{{ route('supplier.postUpdatePrice') }}" method="POST" >
                    {!! csrf_field() !!}
                    <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}" />
                    <input type="hidden" name="product_supplier_id" id="product_supplier_id" value="{{ old('product_supplier_id') }}" />
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Tên sản phẩm</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control " name="product_name" id="product_name" value="{{ old('product_name') }}" placeholder="Nhập tên sản phẩm" autocomplete="off">
                            <ul class="results" id="product_hint" style="display: none">
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Giá bán (có VAT)</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="import_price" value="{{ old('import_price') }}" id="import_price" placeholder="Nhập giá" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">VAT</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="vat" id="vat" value="{{ old('vat') }}" placeholder="Nhập VAT" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Code</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="code" id="code" value="{{ old('code') }}" placeholder="Nhập Code" >
                        </div>

                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Tình trạng</label>
                        <div class="col-sm-6">
                            <select name="state" id="state" class="form-control">
                                @foreach( config('teko.product.state', []) as $key => $value)
                                    <option value="{{$key}}" {{$key == old('state') ? 'selected' : ''}}>{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Ảnh đính kèm</label>
                        <div class="col-sm-3">
                            <input type="file" class="form-control" name="image" id="image" accept="image/*">
                        </div>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#des_editor">Mô tả sản phẩm</button>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label no-padding-left"></label>
                        <button type="submit" class="btn btn-success">
                            <i class="ace-icon fa fa-save bigger-110"></i>Lưu thông tin
                        </button>
                        <a id="bt_cancel" class="btn btn-danger">
                            <i class="ace-icon fa fa-trash bigger-110"></i>Hủy
                        </a>
                    </div>

                    <!-- Modal -->
                    <div id="des_editor" class="modal fade" role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Thông tin sản phẩm</h4>
                                </div>
                                <div class="modal-body">
                                    <textarea name="description" id="description" rows="60" cols="150">
                                    </textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>

                <table id="dataTables-products_suppliers" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top updatePrice">
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
                    <tfoot>
                    <tr>
                        <th><input type="text" style="width: 100%" name="db_category_name" placeholder="Tìm Loại"/></th>
                        <th><input type="text" style="width: 100%" name="db_product_name" placeholder="Tìm Tên sản phẩm"/></th>
                        <th><input type="text" style="width: 100%" name="db_import_price" placeholder="Tìm giá"/></th>
                        <th></th>
                        <th><select name="db_status" style="width: 100%">
                                <option value="">Chọn trạng thái</option>
                                <option value="{{ App\Models\ProductSupplier::$STATUS_KHONG_UU_TIEN_LAY_HANG }}">Chưa ưu tiên lấy hàng</option>
                                <option value="{{ App\Models\ProductSupplier::$STATUS_UU_TIEN_LAY_HANG }}">Ưu tiên lấy hàng</option>
                                <option value="{{ App\Models\ProductSupplier::$STATUS_CHO_DUYET }}">Chờ Duyệt</option>
                                <option value="{{ App\Models\ProductSupplier::$STATUS_HET_HANG }}">Hết hàng</option>
                            </select></th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div><!-- /.page-content -->
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
    <script src="https://cdn.ckeditor.com/4.5.7/standard/ckeditor.js"></script>
@endsection

@section('inline_scripts')
    <script>
        $(function () {
            CKEDITOR.replace( 'description' );
            CKEDITOR.config.height = 500;

            $('#bt_cancel').on( 'click', function (){
                $('#product_id').val("");
                $('#product_name').val("");
                $('#import_price').val("");
                $('#vat').val("");
                $('#code').val("");
                $('#state').val(0);
                $('#product_supplier_id').val("");
                $('#product_hint').val("");
                CKEDITOR.instances.description.setData("");
            });

/*            $("#product_name").blur(function(){
                $('#product_hint').hide();
            });*/

            $("#product_name").focus(function(){
                $('#product_hint').show();
            });

            $("#product_name").blur(function(){
                setTimeout(function() {$('#product_hint').hide();}, 100);
            });

            function getProductById (id,product_name) {
                $.get("{!! route('supplier.ajaxGetProductById') !!}" + "?product_id=" + id , function (respone) {
                    var product = respone.data;
                    $('#product_id').val(id);
                    $('#product_name').val(product_name);
                    if (product) {
                        $('#import_price').val(product.import_price);
                        $('#vat').val(product.vat);
                        $('#code').val(product.code);
                        $('#state').val(product.state);
                        $('#product_supplier_id').val("");
                    }
                    else {
                        $('#import_price').val("");
                        $('#vat').val("");
                        $('#code').val("");
                        $('#state').val(0);
                        $('#product_supplier_id').val("");
                    }
                    CKEDITOR.instances.description.setData(product.description);
                });
                $('#product_hint').hide();
            }

            function productHintClick(event) {
                getProductById(event.data.id,event.data.name);
            }

            $('#product_name').on( 'keyup change', function () {
                $.ajax({
                    url: '{!! route('supplier.ajaxGetProductByName') !!}',
                    data: {
                        product_name : this.value
                    },
                    success: function(result){
                        if (result.status)
                        {
                            $('#product_hint').html('');
                            result.data.forEach(function (product) {
                                $('#product_hint').append(function () {
                                    return $('<li><a>'+ product.name + '<br /><span>SKU: ' + product.sku +'</span></a></li>').click({id: product.id , name: product.name},productHintClick);
                                });
                            })
                        }
                    }
                });
            } );

            $('#dataTables-products tfoot th').each( function () {
                var title = $(this).text();
                if (title)
                $(this).html( '<input type="text" style="width: 100%" placeholder="Tìm '+title+'" />' );
            } );

            var datatable = $("#dataTables-products").DataTable({
                sDom: '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
                info:     false,
                pagingType: "simple_numbers",
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 20, 30, 50, 100],
                ajax: {
                    url: '{!! route('products.datatables') !!}',
                    data: function (d) {
                        d.status = 'active';
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

            $('#dataTables-products tbody').on( 'click', 'td', function () {
                var data = datatable.row( $(this).parents('tr') ).data();
                getProductById(data.id,data.name);
            } );

            datatable.columns().every( function () {
                var that = this;

                $( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that
                            .search( this.value )
                            .draw();
                    }
                } );
            } );

            var supplier_datatable = $("#dataTables-products_suppliers").DataTable({
                searching:false,
                pagingType: "simple_numbers",
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 20, 30, 50, 100],
                ajax: {
                    url: '{!! route('supplier.supplier_datatables') !!}',
                    data: function (d) {
                        d.product_name = $('input[name=db_product_name]').val();
                        d.category_name = $('input[name=db_category_name]').val();
                        d.import_price = $('input[name=db_import_price]').val();
                        d.status = $('select[name=db_status]').val();
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

            supplier_datatable.columns().every( function () {
                $( 'input', this.footer() ).on( 'keyup change', function () {
                    supplier_datatable.draw();
                } );
                $( 'select', this.footer() ).on( 'keyup change', function () {
                    supplier_datatable.draw();
                } );
            } );

            $('#dataTables-products_suppliers tbody').on( 'click', 'a', function () {
                var data = supplier_datatable.row( $(this).parents('tr') ).data();
                $('#product_id').val(data.id);
                $('#product_name').val(data.product_name);
                $('#import_price').val(data.import_price.replace(',',''));
                $('#vat').val(data.vat);
                $('#state').val(data.state);
                $('#code').val(data.code);
                $('#product_supplier_id').val(data.product_supplier_id);
                CKEDITOR.instances.description.setData(data.description);
            } );

            $('.dataTables_filter').hide();

        });
    </script>
@endsection
