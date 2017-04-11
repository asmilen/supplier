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
                <form class="form-horizontal" role="form" id="product_form" action="{{ route('supplier.postUpdatePrice') }}" method="POST" >
                    {!! csrf_field() !!}
                    <input type="hidden" name="product_id" id="product_id" />
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Tên sản phẩm</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="product_name" id="product_name" placeholder="Nhập tên sản phẩm" readonly >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Giá bán (có VAT)</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="import_price" id="import_price" placeholder="Nhập giá" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">VAT</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="vat" id="vat" placeholder="Nhập VAT" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Code</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="code" id="code" placeholder="Nhập Code" >
                        </div>

                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-left">Tình trạng</label>
                        <div class="col-sm-6">
                            <select name="state" id="state" class="form-control">
                                <option value="0">Hết hàng</option>
                                <option value="1">Còn hàng</option>
                                <option value="2">Đặt hàng</option>
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
                        <a onclick="cancel()" class="btn btn-danger">
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
                                    <textarea name="description" id="description" rows="10" cols="80">
                                    </textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>

                        </div>
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
    <script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
    <script src="https://cdn.ckeditor.com/4.5.7/standard/ckeditor.js"></script>
@endsection

@section('inline_scripts')
    <script>
        CKEDITOR.replace( 'description' );

        function cancel() {
            $('#product_id').val("");
            $('#product_name').val("");
            $('#import_price').val("");
            $('#vat').val("");
            $('#code').val("");
            CKEDITOR.instances.description.setData("");
        }

        $(function () {

            $('#dataTables-products tfoot th').each( function () {
                var title = $(this).text();
                if (title)
                $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
            } );

            var datatable = $("#dataTables-products").DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: '{!! route('products.datatables') !!}',
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
                $('#import_price').val("");
                $('#vat').val("");
                $('#code').val("");
                CKEDITOR.instances.description.setData("");
                //$('#product_name').prop('disabled', true);
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
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: '{!! route('supplier.supplier_datatables') !!}',
                    data: function (d) {
                        d.keyword = $('input[name=keyword]').val();
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
                var data = supplier_datatable.row( $(this).parents('tr') ).data();
                $('#product_id').val(data.id);
                $('#product_name').val(data.product_name);
                $('#import_price').val(data.import_price);
                $('#vat').val(data.vat);
                $('#state').val(data.state);
                $('#code').val(data.code);
                CKEDITOR.instances.description.setData(data.description);
            } );
        });
    </script>
@endsection
