@if ($currentUser->hasAccess('attributes.edit'))
<a class="green" href="{{ route('attributes.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
