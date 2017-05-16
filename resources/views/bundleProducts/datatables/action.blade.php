{{--@if ($currentUser->hasAccess('bundleProducts.show'))--}}
{{--<a class="blue" href="{{ route('bundleProducts.show', $id) }}"><i class="ace-icon fa fa-search-plus bigger-130"></i></a>--}}
{{--@endif--}}
@if ($currentUser->hasAccess('bundleProducts.edit'))
<a class="green" href="{{ route('bundleProducts.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
