@if ($status == \App\Models\ProductSupplier::$STATUS_CHO_DUYET)
    @if ($product_id == 0)
        <span class="badge-yellow">Chờ duyệt </span>
    @else
        <span class="badge-yellow">Đã cập nhật </span>
    @endif
@elseif ($status == \App\Models\ProductSupplier::$STATUS_YEU_CAU_UU_TIEN_LAY_HANG)
    <span class="orange">Yêu cầu ưu tiên lấy hàng</span>
@elseif ($status == \App\Models\ProductSupplier::$STATUS_UU_TIEN_LAY_HANG)
    <span class="green">Ưu tiên lấy hàng</span>
@elseif ($status == \App\Models\ProductSupplier::$STATUS_KHONG_UU_TIEN_LAY_HANG)
    <span class="blue">Chưa ưu tiên lấy hàng</span>
@elseif ($status == \App\Models\ProductSupplier::$STATUS_HET_HANG)
    <span class="red">Hết hàng</span>
@endif
