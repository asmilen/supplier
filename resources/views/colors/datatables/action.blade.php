@if ($currentUser->hasAccess('colors.show'))
<a class="blue" href="{{ route('colors.show', $id) }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('colors.edit'))
<a class="green" href="{{ route('colors.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
