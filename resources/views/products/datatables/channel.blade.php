@foreach(explode(",",$channel) as $channel)
    {{config('teko.stores')[$channel]}} @if (!$loop->last), @endif
@endforeach
