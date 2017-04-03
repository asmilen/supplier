@if ($currentUser->hasAccess('manufacturers.show'))
<a class="blue" href="{{ route('manufacturers.show', $id) }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('manufacturers.edit'))
<a class="green" href="{{ route('manufacturers.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('manufacturers.destroy'))
<a class="red" id="btn-delete-{{ $id }}" data-url="{{ route('manufacturers.destroy', $id) }}" href="javascript:;"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
@endif
