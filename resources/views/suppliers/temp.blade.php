<form class="form-horizontal" role="form" id="supplier_form" action="{{ url('suppliers/updateStatus') }}" method="POST" enctype="multipart/form-data" >
    {!! csrf_field() !!}

    <table id="dataTables-products" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
        <thead>
            <tr>
                <th >STT</th>
                <th >Ảnh</th>
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
        <tbody>
                @foreach($products as $key => $value)
                    <tr>
                        <input type="hidden" name = "product_supplier_id[]" value="{{ $value->id }}"/>
                        <input type="hidden" name = "best_price[]" value="{{ $value->import_price }}"/>
                        <input type="hidden" name = "product[]" value="{{ $value->product_id }}"/>
                        <td>{{ $key + 1 }}</td>
                        <td><img src="{{ $value->image }}"></td>
                        <td>{{ $value->product_name }}</td>
                        <td>{{ $value->import_price }}</td>
                        <td>{{ $value->vat }}</td>
                        <td>{{ $value->recommend_price }}</td>
                        <td>
                            <select name="status[]">
                                    <option value="0" <?php if($value->status == 0) echo 'selected'?>>Chờ duyệt</option>
                                    <option value="1" <?php if($value->status == 1) echo 'selected'?>>Hết hàng</option>
                                    <option value="2" <?php if($value->status == 2) echo 'selected'?>>Ưu tiên lấy hàng</option>
                                    <option value="3" <?php if($value->status == 3) echo 'selected'?>>Yêu cầu ưu tiên lấy hàng</option>
                                    <option value="4" <?php if($value->status == 4) echo 'selected'?>>Không ưu tiên lấy hàng</option>
                            </select>
                        </td>
                        <td>{{ $value->supplier_name }}</td>
                        <td>
                            @if($value->state == 0)
                                   {!! 'Hết hàng' !!}
                            @elseif($value->state == 1)
                                {!! 'Còn hàng' !!}
                            @else
                                {!! 'Đặt hàng' !!}
                            @endif
                        </td>
                        <td>{{ $value->updated_at }}</td>
                    </tr>
                @endforeach
        </tbody>
    </table>
    <br>
    <div class="form-group">
        <label class="col-sm-4 control-label no-padding-left"></label>
        <button type="submit" class="btn btn-success" id = "btn_save">
            <i class="ace-icon fa fa-save bigger-110"></i>Lưu thông tin
        </button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
    </div>
</form>

<script>
    var datatable = $("#dataTables-products").DataTable({
    });
</script>

