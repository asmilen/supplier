@extends('layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.2.1/css/select.dataTables.min.css">
    <link rel="stylesheet" href="https://editor.datatables.net/extensions/Editor/css/editor.dataTables.min.css">
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
            <table id="dataTables-products" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Nhà cung cấp</th>
                        <th>Tình trạng hàng</th>
                        <th>Giá nhập</th>
                        <th>GTGT</th>
                        <th>Giá Saler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $val)
                        <tr>
                            <td>{{$key + 1}}</td>
                            <td>{{$val->supplier_name}}</td>
                            <td>{{$val->status}}</td>
                            <td>{{$val->import_price}}</td>
                            <td>{{$val->vat}}</td>
                            <td>{{ ($val->import_price + $val->vat) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-xs-12">
            @include('common.errors')
            <form class="form-horizontal" role="form" id="product_form" action="{{ route('suppliers.store') }}" method="POST" enctype="multipart/form-data" >
                {!! csrf_field() !!}
                <input type="hidden" name="product_id" id="product_id" value="{{ $id }}"/>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-left">Nhà cung cấp</label>
                    <div class="col-sm-6">
                        <select name="supplier_id" id="supplier_id" class="form-control">
                            <option value="">-- Chọn nhà cung cấp --</option>
                            @foreach($suppliers as $key => $value)
                                <option value="{{  $value->id }}">{{  $value->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-left">Tình trạng</label>
                    <div class="col-sm-6">
                        <select name="state" id="state" class="form-control">
                            <option value="">-- Chọn tình trạng --</option>
                            <option value="0">Hết hàng</option>
                            <option value="1">Còn hàng</option>
                            <option value="2">Đặt hàng</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-left">Giá nhập (có VAT)</label>
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
                    <label class="col-sm-3 control-label no-padding-left">Giá bán</label>
                    <div class="col-sm-6">
                        <input type="number" class="form-control" name="saler_price" id="import_price" placeholder="Nhập giá" >
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-left">Số lượng</label>
                    <div class="col-sm-6">
                        <input type="number" class="form-control" name="quantity" id="quantity" placeholder="Nhập số lượng" >
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-left">Ảnh</label>
                    <div class="col-sm-6">
                        <input type="file" class="form-control" name="image"  >
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-left">Mô tả</label>
                    <div class="col-sm-6">
                        <textarea class="form-control" name="description" id="description"></textarea>
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
    });
    CKEDITOR.replace('description');

    @include('scripts.click-datatable-delete-button')
});
</script>
@endsection
