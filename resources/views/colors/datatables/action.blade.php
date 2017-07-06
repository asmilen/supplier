@if ($currentUser->hasAccess('colors.edit'))
<a class="green" href="{{ route('colors.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
