@if ($currentUser->hasAccess('attributes.show'))
<a class="blue" href="{{ route('attributes.show', $id) }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('attributes.edit'))
<a class="green" href="{{ route('attributes.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
