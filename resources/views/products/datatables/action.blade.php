@if ($currentUser->hasAccess('products.saleprice.show'))
<a class="red" href="{{ route('products.saleprice.show', $id) }}" title="Đặt giá bán"><i class="ace-icon fa fa-money bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('products.edit'))
<a class="green" href="{{ route('products.edit', $id) }}" title="Sửa"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
