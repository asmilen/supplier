<html>
<head></head>
<body>
<p>Dear {{$name}}, </p>
<p>Những sản phẩm sau có hiệu lực giá hết hạn trong 24h tới, vui lòng kiểm tra:</p>
@foreach ($products as $product)
    <p> {{$product}}</p>
@endforeach
</body>
</html>