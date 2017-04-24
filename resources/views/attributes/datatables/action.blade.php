@if ($currentUser->hasAccess('attributes.show'))
<a class="blue" href="{{ route('attributes.show', $id) }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('attributes.edit'))
<a class="green" href="{{ route('attributes.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('attributes.destroy'))
<a class="red" id="btn-delete-{{ $id }}" data-url="{{ route('attributes.destroy', $id) }}" href="javascript:;"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
@endif
