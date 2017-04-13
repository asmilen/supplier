@extends('layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.2.1/css/select.dataTables.min.css">
    <link rel="stylesheet" href="https://editor.datatables.net/extensions/Editor/css/editor.dataTables.min.css">
    <style>
        .tooltip2 {
            position: relative;
            display: inline-block;
        }

        .tooltip2 .tooltiptext {
            visibility: hidden;
            width: auto;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            /*padding: 5px 0;*/
            position: absolute;
            z-index: 1;
            bottom: 125%;
            opacity: 0;
            transition: opacity 1s;
        }

        .tooltip2 .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }

        .tooltip2:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>
@endsection
@section('content')
<!-- #section:basics/content.breadcrumbs -->
<div class="breadcrumbs" id="breadcrumbs">
    <script type="text/javascript">
        try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
    </script>
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
                            <h4 class="modal-title">Thêm mới nhà cung cấp</h4>
                        </div>
                        <div class="modal-body">
                                <form class="form-horizontal" role="form" id="supplier_form" action="{{ url('suppliers/map-suppliers') }}" method="POST" enctype="multipart/form-data" >
                                {!! csrf_field() !!}
                                <input type="hidden" name="product_id" id="product_id" value="{{ $id }}"/>
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
                                    <label class="col-sm-3 control-label no-padding-left">Tình trạng</label>
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
                                    <label class="col-sm-3 control-label no-padding-left">Giá bán</label>
                                    <div class="col-sm-9">
                                        <input type="number" min = "0" class="form-control" name="price_recommend" placeholder="Nhập giá" >
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
                        <th>STT</th>
                        <th>Nhà cung cấp</th>
                        <th>Tình trạng hàng</th>
                        <th>Giá nhập</th>
                        <th>GTGT</th>
                        <th>Giá khuyến nghị</th>
                        <th>Ảnh</th>
                        <th>Mô tả</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $val)
                        <tr>
                            <td>{{$key + 1}}</td>
                            <td>{{$val->supplier_name}}</td>
                            <td>
                                @if($val->state == 0)
                                    {!! 'Hết hàng' !!}
                                @elseif($val->state == 1)
                                    {!! 'Còn hàng' !!}
                                @else
                                    {!! 'Đặt hàng' !!}
                                @endif
                            </td>
                            <td>{{ number_format($val->import_price) }}</td>
                            <td>{!!  number_format($val->vat)  !!}</td>
                            <td>{{ number_format($val->recommend_price)  }}</td>
                            <td><img src="{{ url('storage/'.$val->image) }}" style="width: 100px;"/></td>
                            <td>
                                <div class="tooltip2"><span class="tooltip_desc">{!! strip_tags($val->description) !!}</span>
                                    <span class="tooltiptext">{!! html_entity_decode($val->description)  !!} </span>
        </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
    <script src="//cdn.ckeditor.com/4.5.7/standard/ckeditor.js"></script>
<script>
$(function () {
    var datatable = $("#dataTables-products").DataTable({
        "bSort" : false
    });
    CKEDITOR.replace('_description');
    $("span.tooltip_desc").text(function(index, currentText) {
        return currentText.substr(0, 200);
    });
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
                $("#supplier_id, #state, #import_price , #vat , #price_recommend , #quantity, #image, #description").text('');
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
                } else {
                    $.each(res.errors,function(index, value) {
                        $("#"+index).text(value);
                    });
                }

            }
        });
    });

    @include('scripts.click-datatable-delete-button')
});
</script>
@endsection
