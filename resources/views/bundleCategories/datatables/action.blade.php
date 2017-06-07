@if ($currentUser->hasAccess('bundleCategories.edit'))
<a class="green" href="{{ route('bundleCategories.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
