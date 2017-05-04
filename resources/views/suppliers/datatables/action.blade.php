@if ($currentUser->hasAccess('suppliers.show'))
<a class="blue" href="{{ route('suppliers.show', $id) }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('suppliers.edit'))
<a class="green" href="{{ route('suppliers.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
