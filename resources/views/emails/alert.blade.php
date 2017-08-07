<html>
<head></head>
<body>
<p>Dear team, </p>
<p>Những sản phẩm sau có hiệu lực giá hết hạn trong 24h tới, vui lòng kiểm tra:</p>
<table style=" border: none;">
    <thead>
        <tr>
            <th>Tên sản phẩm</th>
            <th>Tên nhà cung cấp</th>
            <th>Giá nhập</th>
            <th>Cập nhật lần cuối lúc</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($products as $product)
        <tr>
            <td>{{ $product->name }}</td>
            <td>{{ $product->supplier ? $product->supplier->name : '' }}</td>
            <td>{{ number_format($product->import_price , 0 , "." , ",") }}</td>
            <td>{{ $product->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>