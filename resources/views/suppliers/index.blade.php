@extends('layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://editor.datatables.net/extensions/Editor/css/editor.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.css">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
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
        tfoot {
            display: table-header-group;
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
            <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Thêm mới</button>
            <!-- Modal -->
            <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Thêm mới nhà sản phẩm cho nhà cung cấp</h4>
                        </div>
                        <div class="modal-body">
                            <form class="form-horizontal" role="form" id="supplier_form" action="{{ url('suppliers/map-suppliers') }}" method="POST" enctype="multipart/form-data" >
                                {!! csrf_field() !!}
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">Sản phẩm</label>
                                    <div class="col-sm-9">
                                        <select name="product_id" class="js-example-basic-single" id="js-example-basic-single">
                                            <option value="">-- Chọn sản phẩm --</option>
                                            @foreach($products as $key => $value)
                                                <option value="{{  $value->id }}">{{  $value->name }}</option>
                                            @endforeach
                                        </select>
                                        <p style="color:red;text-align: left;" id="product_id">{{$errors->first('product_id')}}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">Nhà cung cấp</label>
                                    <div class="col-sm-9">
                                        <select name="supplier_id" class="form-control">
                                            <option value="">-- Chọn nhà cung cấp --</option>
                                            @foreach($suppliers as $key => $value)
                                                <option value="{{  $value->id }}">{{  $value->name }}</option>
                                            @endforeach
                                        </select>
                                        <p style="color:red;text-align: left;" id="supplier_id">{{$errors->first('supplier_id')}}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">Tình trạng nhà cung cấp</label>
                                    <div class="col-sm-9">
                                        <select name="status"  class="form-control">
                                            <option value="">-- Chọn tình trạng --</option>
                                            <option value="0">Chờ duyệt</option>
                                            <option value="1">Hết hàng</option>
                                            <option value="2">Ưu tiên lấy hàng</option>
                                            <option value="3">Yêu cầu ưu tiên lấy hàng</option>
                                            <option value="4">Không ưu tiên lấy hàng</option>
                                        </select>
                                        <p style="color:red;text-align: left;" id="state">{{$errors->first('status')}}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">Tình trạng sản phẩm</label>
                                    <div class="col-sm-9">
                                        <select name="state"  class="form-control">
                                            <option value="">-- Chọn tình trạng --</option>
                                            <option value="0">Hết hàng</option>
                                            <option value="1">Còn hàng</option>
                                            <option value="2">Đặt hàng</option>
                                        </select>
                                        <p style="color:red;text-align: left;" id="state">{{$errors->first('state')}}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">Giá nhập (có VAT)</label>
                                    <div class="col-sm-9">
                                        <input type="number" min = "0" class="form-control" name="import_price" placeholder="Nhập giá" >
                                        <p style="color:red;text-align: left;" id="import_price">{{$errors->first('import_price')}}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">VAT</label>
                                    <div class="col-sm-9">
                                        <input type="number" min = "0" class="form-control" name="vat" placeholder="Nhập VAT" >
                                        <p style="color:red;text-align: left;" id="vat">{{$errors->first('vat')}}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">Giá bán khuyến nghị</label>
                                    <div class="col-sm-9">
                                        <input type="number" min = "0" class="form-control" name="price_recommend" placeholder="Nhập giá bán khuyến nghị" >
                                        <p style="color:red;text-align: left;" id="price_recommend">{{$errors->first('price_recommend')}}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">Số lượng</label>
                                    <div class="col-sm-9">
                                        <input type="number" min = "0" class="form-control" name="quantity"  placeholder="Nhập số lượng" >
                                        <p style="color:red;text-align: left;" id="quantity">{{$errors->first('quantity')}}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">Ảnh</label>
                                    <div class="col-sm-9">
                                        <input type="file" class="form-control" name="image"  >
                                        <p style="color:red;text-align: left;" id="image">{{$errors->first('image')}}</p>
                                    </div>

                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-left">Mô tả</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" name="description" id = "_description"></textarea>
                                        <p style="color:red;text-align: left;" id="description">{{$errors->first('description')}}</p>
                                    </div>

                                </div>


                                <div class="form-group">
                                    <label class="col-sm-4 control-label no-padding-left"></label>
                                    <button type="button" class="btn btn-success" id = "btn_save">
                                        <i class="ace-icon fa fa-save bigger-110"></i>Lưu thông tin
                                    </button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-xs-12">
            <table id="dataTables-products" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th >Danh mục</th>
                        <th >Nhà sản xuất</th>
                        <th >SKU</th>
                        <th >Tên</th>
                        <th >Giá nhập</th>
                        <th >GTGT</th>
                        <th >Giá bán khuyến nghị</th>
                        <th >Trạng thái </th>
                        <th >Nhà cung cấp</th>
                        <th >Tình trạng</th>
                        <th >Ngày cập nhật</th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <th><input type="text" style="width: 100%" name="db_category_name" placeholder=""/></th>
                        <th><input type="text" style="width: 100%" name="db_manufacture_name" placeholder=""/></th>
                        <th><input type="text" style="width: 100%" name="db_product_sku" placeholder=""/></th>
                        <th><input type="text" style="width: 100%" name="db_product_name" placeholder=""/></th>
                        <th><input type="text" style="width: 100%" name="db_product_import_price" placeholder=""/></th>
                        <th><input type="text" style="width: 100%" name="db_product_vat" placeholder=""/></th>
                        <th><input type="text" style="width: 100%" name="db_product_recommend_price" placeholder=""/></th>
                        <th><select name="db_status" style="width: 100%">
                                <option value=""></option>
                                <option value="0">Chờ duyệt</option>
                                <option value="1">Hết hàng</option>
                                <option value="2">Ưu tiên lấy hàng</option>
                                <option value="3">Yêu cầu ưu tiên lấy hàng</option>
                                <option value="4">Không ưu tiên lấy hàng</option>
                            </select></th>
                        <th><input type="text" style="width: 100%" name="db_supplier_name" placeholder=""/></th>

                        <th><select name="db_state" style="width: 100%">
                                <option value=""></option>
                                <option value="{{ App\Models\ProductSupplier::$STATE_HET_HANG }}">Hết hàng</option>
                                <option value="{{ App\Models\ProductSupplier::$STATE_CON_HANG }}">Còn hàng</option>
                                <option value="{{ App\Models\ProductSupplier::$STATE_DAT_HANG }}">Đặt hàng</option>
                            </select></th>
                        <th ><input class="form-control input-daterange-datepicker" type="text" name="db_updated_at"
                                    value="" placeholder="Từ ngày" style="width: 200px;"/></th>
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/dataTables.cellEdit.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.min.js"></script>
    <script src="//cdn.ckeditor.com/4.5.7/standard/ckeditor.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
@endsection

@section('inline_scripts')

    <script>
        $(function () {
            $(".js-example-basic-single").select2({
                placeholder: "-- Chọn sản phẩm --",
                allowClear: true,
                width: '100%'
            });

            var datatable = $("#dataTables-products").DataTable({
                "searching": false,
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 50,
                "bSort" : false,
                "bDestroy": true,
                ajax: {
                    url: '{!! route('suppliers.datatables') !!}',
                    data: function (d) {
                        d.category_name = $('input[name=db_category_name]').val();
                        d.manufacture_name = $('input[name=db_manufacture_name]').val();
                        d.product_sku = $('input[name=db_product_sku]').val();
                        d.product_name = $('input[name=db_product_name]').val();
                        d.product_import_price = $('input[name=db_product_import_price]').val();
                        d.vat = $('input[name=db_product_vat]').val();
                        d.recommend_price = $('input[name=db_product_recommend_price]').val();
                        d.status = $('select[name=db_status]').val();
                        d.supplier_name = $('input[name=db_supplier_name]').val();
                        d.state = $('select[name=db_state]').val();
                        d.updated_at = $('input[name=db_updated_at]').val();
                    }
                },
                columns: [
                    {data: 'cat_name', name: 'cat_name',"width": "10%"},
                    {data: 'manufacturer_name', name: 'manufacturer_name',"width": "5%"},
                    {data: 'sku', name: 'sku',"width": "10%"},
                    {data: 'product_name', name: 'product_name',"width": "15%"},
                    {data: 'import_price', name: 'import_price',"width": "5%"},
                    {data: 'vat', name: 'vat',"width": "5%"},
                    {data: 'recommend_price', name: 'recommend_price',"width": "5%"},
                    {data: 'status',name: 'status',"width": "10%"},
                    {data: 'supplier_name',name: 'supplier_name',"width": "10%"},
                    {data: 'status_product',name: 'status_product',"width": "5%"},
                    {data: 'updated_at',name: 'updated_at',"width": "5%"},
                ],
            });

            datatable.MakeCellsEditable({
                "onUpdate": myCallbackFunction,
                "inputCss":'my-input-class',
                "idSrc":  'id',
                "columns": [7],
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
                        "column":7,
                        "type": "list",
                        "options":[
                            { "value": "Chờ duyệt", "display": "Chờ duyệt" },
                            { "value": "Hết hàng", "display": "Hết hàng" },
                            { "value": "Ưu tiên lấy hàng", "display": "Ưu tiên lấy hàng" },
                            { "value": "Yêu cầu ưu tiên lấy hàng'", "display": "Yêu cầu ưu tiên lấy hàng'" },
                            { "value": "Không ưu tiên lấy hàng'", "display": "Không ưu tiên lấy hàng'" }
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

            datatable.columns().every( function () {
                $( 'input', this.footer() ).on( 'keyup change', function () {
                    datatable.draw();
                } );
                $( 'select', this.footer() ).on( 'keyup change', function () {
                    datatable.draw();
                } );
                $('.input-daterange-datepicker').on('apply.daterangepicker', function(ev, picker){
                    var startDate = picker.startDate;
                    var endDate = picker.endDate;
                    $('.input-daterange-datepicker').val(startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY'));
                    datatable.draw();
                } );
            } );

            CKEDITOR.replace('_description');

            $("#btn_save").on("click", function () {
                for ( instance in CKEDITOR.instances )
                    CKEDITOR.instances[instance].updateElement();
                var form = $('#supplier_form');
                var data = new FormData(form[0]);
                $.ajax({
                    headers: { 'X-CSRF-Token': $('input[name="_token"]').val() },
                    url: $('#supplier_form').attr('action'),
                    type: $('#supplier_form').attr('method'),
                    data: data,
                    processData: false,
                    cache:false,
                    contentType:false,
                    dataType: 'JSON',
                    success: function (res){
                        $("#product_id,#supplier_id, #status, #state, #import_price , #vat , #price_recommend , #quantity, #image, #description").text('');
                        if(res.status == 'success'){
                            $('#myModal').modal('hide');
                            swal({
                                    title: "Tạo thành công",
                                    type: "success",
                                    showCancelButton: false,
                                    confirmButtonColor: "#DD6B55",
                                    confirmButtonText: "Ok",
                                    closeOnConfirm: false
                                },
                                function(){
                                    window.location.reload();
                                });
                        } else if(res.status == "exists") {
                            $('#myModal').modal('hide');
                            swal({
                                    title: "Tạo không thành công",
                                    'text': "Nhà cung cấp này đã bán sản phẩm",
                                    type: "warning",
                                    showCancelButton: false,
                                    confirmButtonColor: "#DD6B55",
                                    confirmButtonText: "Ok",
                                    closeOnConfirm: false
                                },
                                function(){
                                    window.location.reload();
                                });
                        }
                        else {
                            $.each(res.errors,function(index, value) {
                                $("#"+index).text(value);
                            });
                        }

                    }
                });
            });

            $('.input-daterange-datepicker').daterangepicker({
                autoUpdateInput: false,
                dateLimit: {
                    days: 60
                },
                showDropdowns: true,
                showWeekNumbers: true,
                timePicker: false,
                timePickerIncrement: 1,
                timePicker12Hour: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment()],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                opens: 'left',
                drops: 'down',
                buttonClasses: ['btn', 'btn-sm'],
                applyClass: 'btn-default',
                cancelClass: 'btn-white',
                separator: ' to ',
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Submit',
                    cancelLabel: 'Clear',
                    fromLabel: 'From',
                    toLabel: 'To',
                    customRangeLabel: 'Custom',
                    daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    firstDay: 1
                }
            });
            $('.input-daterange-datepicker').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });

            $('.input-daterange-datepicker').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });

            @include('scripts.click-datatable-delete-button')
        });
    </script>
@endsection
