@if ($currentUser->hasAccess('userPermissions.index'))
<a class="orange" href="{{ route('userPermissions.index', $id) }}"><i class="ace-icon fa fa-lock bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('users.edit'))
<a class="green" href="{{ route('users.edit', $id) }}"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
@endif
@if ($currentUser->hasAccess('users.destroy'))
<a class="red" id="btn-delete-{{ $id }}" data-url="{{ route('users.destroy', $id) }}" href="javascript:;"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
@endif
