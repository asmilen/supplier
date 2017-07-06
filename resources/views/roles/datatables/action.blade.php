@if ($currentUser->hasAccess('rolePermissions.index'))
<a class="orange" href="{{ route('rolePermissions.index', $id) }}"><i class="ace-icon fa fa-lock bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('roles.edit'))
<a class="green" href="{{ route('roles.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('roles.destroy'))
<a class="red" id="btn-delete-{{ $id }}" data-url="{{ route('roles.destroy', $id) }}" href="javascript:;"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
@endif
