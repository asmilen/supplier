@if ($currentUser->hasAccess('combo.edit'))
<a class="green" href="{{ route('combo.edit', $id) }}" title="Sửa"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
