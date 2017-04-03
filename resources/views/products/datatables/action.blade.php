@if ($currentUser->hasAccess('products.show'))
<a class="blue" href="{{ route('products.show', $id) }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('products.edit'))
<a class="green" href="{{ route('products.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('products.destroy'))
<a class="red" id="btn-delete-{{ $id }}" data-url="{{ route('products.destroy', $id) }}" href="javascript:;"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
@endif
