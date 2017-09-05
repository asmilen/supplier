@if (!! $status)
<a data-url="{{ route('bundles.status.toggle', $id) }}" href="javascript:;" id="btn-toggle-{{ $id }}"><i class="ace-icon fa bigger-130 fa-check-circle-o green"></i></a>
@else
<a data-url="{{ route('bundles.status.toggle', $id) }}" href="javascript:;" id="btn-toggle-{{ $id }}"><i class="ace-icon fa bigger-130 fa-times-circle-o red"></i></a>
@endif
