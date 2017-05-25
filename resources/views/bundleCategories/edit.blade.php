@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/2.1.0/select2.css">
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
            <a href="{{ route('bundleCategories.index') }}">Nhóm sản phẩm </a>
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
            <a class="btn btn-primary pull-right" href="{{ route('bundleCategories.index') }}">
                <i class="ace-icon fa fa-list" aria-hidden="true"></i>
                <span class="hidden-xs">Danh sách</span>
            </a>
        </h1>
    </div><!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            @include('common.errors')

            <form class="form-horizontal" role="form" method="POST" action="{{ route('bundleCategories.update', $bundleCategory->id) }}">
                {!! method_field('PUT') !!}
                {!! csrf_field() !!}
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Nhóm sản phẩm</label>
                    <div class="col-sm-6">
                        <select name="bundle_id" class="form-control" id = "bundleId" {{ $bundleProducts ?  'disabled=disabled' : ''}}>
                            <option value="">--Chọn nhóm sản phẩm--</option>
                            @foreach ($bundlesList as $id => $name)
                                <option value="{{ $id }}" {{ $id == $bundleCategory->id_bundle ? 'selected=selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Tên danh mục theo nhóm sản phẩm</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="name" placeholder="Tên danh mục ...." value="{{ old('name', $bundleCategory->name) }}">
                    </div>
                </div>

                <label class="control-label no-padding-right">Sản phẩm trong nhóm sản phẩm</label>
                <br>
                <div>
                    <table class="table">
                        <thead>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Sku</th>
                        <th>Số lượng</th>
                        <th>Mặc định</th>
                        <th>Thao tác</th>
                        </thead>
                        <tbody>
                        @if($bundleProducts)
                            @foreach($bundleProducts as $key => $bundleProduct)
                                <tr>
                                    <input type ="hidden" name= "productIds[]" value="{{ $bundleProduct->id }}"/>
                                    <td>{{ $bundleProduct->id }}</td>
                                    <td>{{ $bundleProduct->name }}</td>
                                    <td>{{ $bundleProduct->sku }}</td>
                                    <td><input name="quantity[]" type="number" min = 0 value="{{ $bundleProduct->pivot->quantity }}"/></td>
                                    <td><input type="radio" name="default" value="{{ $bundleProduct->id }}" {{ $bundleProduct->pivot->is_default == 1 ? ' checked=checked' : '' }}/></td>
                                    <td><a class="deleteProduct" data-categoryId ="{{ $bundleCategory->id }}" data-productId ="{{ $bundleProduct->id }}" data-bundleId = "{{  $bundleProduct->pivot->id_bundle  }}" href=""><i class="fa fa-trash-o" aria-hidden="true"></i></a></td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                        <tfoot id = "bundleProducts">
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
                                                <th >ID</th>
                                                <th >Tên</th>
                                                <th >SKU</th>
                                                <th >Chọn </th>
                                                <th >Số Lượng</th>
                                                <th >Mặc định</th>
                                            </tr>
                                            </thead>
                                            <tbody id="productsRegion">
                                            @foreach($products as $key => $value)
                                                <tr class="tr">
                                                    <td>{{ $value->id }}</td>
                                                    <td class="productName">{{ $value->name }}</td>
                                                    <td class="productSku">{{ $value->sku }}</td>
                                                    <td><input  type="checkbox" value="{{ $value->id }}" class="checkbox"/></td>
                                                    <td><input  class="qty"  type="number" min = 0/></td>
                                                    <td><input  class="radio" type="radio"  value="{{ $value->id }}" name="default"/></td>
                                                </tr>
                                            @endforeach
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
            var table = '';
            table = $("#tableproducts").DataTable({
                autoWidth: false
            });

            $('#bundleId').on('change', function (e) {
                loadProduct(this.value);
            });

            function loadProduct(bundleId) {
                table.destroy();
                $.ajax({
                    url: "/region/" + bundleId + "/products",
                    success: function(products) {
                        $("#productsRegion").html('');
                        $.each(products, function(key, product) {
                            $("#productsRegion").append('<tr>' +
                                '<td>' + product.id + '</td>' +
                                '<td class="productName">' + product.name + '</td>' +
                                '<td class="productSku">'+ product.sku +'</td>' +
                                '<td><input  type="checkbox" value="' + product.id + '" class="checkbox"/></td>' +
                                '<td><input  class="qty"  type="number" min = 0/></td>' +
                                '<td><input  class="radio" type="radio"  value="' + product.id + '" name="default"/></td>'
                                + '</tr>');
                        });
                        table = $("#tableproducts").DataTable({
                            autoWidth: false
                        });
                    },
                    error: function() {

                    }
                });
            }

            $('.deleteProduct').click( function (e) {
                e.preventDefault();
                var bundleId = $(this).attr('data-bundleId');
                var productId = $(this).attr('data-productId');
                var categoryId = $(this).attr('data-categoryId');
                var r = confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi nhóm sản phẩm!");
                if (r == true) {
                    $(this).closest('tr').remove();
                    $.ajax({
                        url: "{{ url('bundleProducts/destroy') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            productId : productId,
                            bundleId : bundleId,
                            categoryId : categoryId,
                        },
                        dataType: "json",
                        success: function(response){
                            if (response.countProduct == 0) {
                                $("#bundleId").removeAttr("disabled");
                            }
                        }
                    });
                }
            });

            $(document).on('click', '#btnChooseProduct', function(e) {
                var productNames = [];
                var productIds = [];
                var productSkus = [];
                var productQtys = [];
                var productDefaults = [];
                var rowcollection =  table.$(".checkbox:checked", {"page": "all"});
                for(var i = 0; i < rowcollection.length; i++)
                {
                    productNames.push($(rowcollection[i]).closest('tr').find('.productName').text());
                    productIds.push($(rowcollection[i]).val());
                    productSkus.push($(rowcollection[i]).closest('tr').find('.productSku').text());
                    productQtys.push($(rowcollection[i]).closest('tr').find('.qty').val());
                    productDefaults.push($(rowcollection[i]).closest('tr').find('.radio').val());
                }

                $("#bundleProducts").html('');

                for(var i = 0; i < productNames.length; i++) {
                    var checked = '';
                    if(productDefaults[i] == productIds[i]) {
                        checked = 'checked';
                    }
                    $("#bundleProducts").append('<tr>' +
                        '<input type ="hidden" name= "productIds[]" value="' +productIds[i] + '"/>' +
                        '<td>' + productIds[i] + '</td>'   +
                        '<td>' + productNames[i] + '</td>' +
                        '<td>' + productSkus[i] + '</td>'  +
                        '<td><input type = "number" name = "quantity[]" min = 0 value="' + productQtys[i] + '"/></td>'  +
                        '<td><input type="radio" name="default" value="' + productIds[i] + '"' + checked + '/></td>'  +
                        '<td><a class="removeProduct" href=""><i class="fa fa-trash-o" aria-hidden="true"></i></a></td>'  +
                        + '</tr>');
                }

                $("#myModalProduct").hide();
            });

            $(document).on('click', '.removeProduct', function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
            });
        });

    </script>
@endsection
