@foreach(explode(",",$channel) as $channel)
    <?php if(isset(config('teko.stores')[$channel])) echo config('teko.stores')[$channel]; ?> @if (!$loop->last), @endif
@endforeach
