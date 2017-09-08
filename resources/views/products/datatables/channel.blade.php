@foreach(explode(",",$channel) as $channel)
    {{config('teko.product.channel')[$channel]}} @if (!$loop->last), @endif
@endforeach
