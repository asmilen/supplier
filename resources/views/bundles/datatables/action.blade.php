@if ($currentUser->hasAccess('bundles.show'))
<a class="blue" href="{{ route('bundles.show', $id) }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('bundles.edit'))
<a class="green" href="{{ route('bundles.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
