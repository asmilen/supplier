@if ($label == 1)
    {{ 'Máy chủ' }}
@elseif ($label == 2)
    {{ 'Máy trạm' }}
@else
    {{ 'Linh kiện, phụ kiện khác' }}
@endif