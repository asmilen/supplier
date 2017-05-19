@if ($currentUser->hasAccess('bundleCategories.show'))
<a class="blue" href="{{ route('bundleCategories.show', $id) }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('bundleCategories.edit'))
<a class="green" href="{{ route('bundleCategories.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('bundleProducts.create'))
    <a class="green" href="{{ route('bundleProducts.create', $id) }}"><i class="ace-icon fa fa-plus bigger-130"></i></a>
@endif
