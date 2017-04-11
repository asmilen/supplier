@if ($status == \App\Models\ProductSupplier::$STATUS_CHO_DUYET)
    Chờ duyệt
@elseif ($status == \App\Models\ProductSupplier::$STATUS_CAP_NHAT)
    Cập nhật
@elseif ($status == \App\Models\ProductSupplier::$STATUS_DA_DANG)
    Đã Đăng
@elseif ($status == \App\Models\ProductSupplier::$STATUS_YEU_CAU_DANG)
    Yêu cầu Đăng
@endif
