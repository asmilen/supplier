@if ($region_id == 1)
    {{ 'Miền bắc' }}
@elseif ($region_id == 2)
    {{ 'Miền trung' }}
@else
    {{ 'Miền nam' }}
@endif