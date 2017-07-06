@if ($currentUser->hasAccess('bundles.edit'))
<a class="green" href="{{ route('bundles.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
