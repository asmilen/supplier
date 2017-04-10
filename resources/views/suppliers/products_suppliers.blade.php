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

    var datatable = $("#dataTables-products").DataTable({
    });


    @include('scripts.click-datatable-delete-button')
});
</script>
@endsection
