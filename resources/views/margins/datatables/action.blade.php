@if ($currentUser->hasAccess('margins.edit'))
<a class="green" href="{{ route('margins.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
