<?php
namespace App\Transformers;

use App\Models\MarginRegionCategory;
use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\SupplierSupportedProvince;
use App\Models\TransportFee;
use \League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\DB;

class ProductApiTransformer extends TransformerAbstract
{

    public function __construct($provinceIds, $code)
    {
        $this->provinceIds = $provinceIds;
        $this->code = $code;
    }

    /**
     * @param array $data
     * @return array
     */
    public function transform($data)
    {
        $regions = Province::whereIn('code', request('province_ids'))->pluck('region_id'); // tìm miền của tỉnh mua hàng
        
        $product = Product::with('manufacturer', 'category')
            ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
            ->leftJoin('product_supplier', function ($q) use ($regions) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->where('product_supplier.region_id', $regions[0])
                    ->where('product_supplier.state', '=', 1);
            })
            ->findOrFail($data['id']);// kiểm tra thông tin sản phẩm cần mua

        $margin = MarginRegionCategory::where('category_id', $product->category_id)
            ->whereIn('region_id', $regions)->first(); // tính margin của category sản phẩm

        $marginValue = ($margin ? 1 + 0.01 * $margin->margin : 1.05); // giá trị của margin

        $province = Province::whereIn('code', request('province_ids'))->pluck('id'); // tìm tỉnh mua hàng

        $provinceFee = TransportFee::where('province_id', $province ? $province[0] : 0)->first();// phí ship của tỉnh mua hàng

        $feeValue = ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01; //giá trị của phí ship của tỉnh mua hàng


        $minPrice = ProductSupplier::where('product_id', $data['id'])
            ->where('product_supplier.region_id',  $regions[0])
            ->leftJoin('supplier_supported_province', 'product_supplier.supplier_id', '=', 'supplier_supported_province.supplier_id')
            ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
            ->where('product_supplier.state', '=', 1)
            ->orderBy(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, product_supplier.import_price * (' .
                $marginValue . '+' . $feeValue . '+' . '(case when supplier_supported_province.province_id = ' . $province[0]  . ' then 0 else if(transport_fees.percent_fee is null, 0,transport_fees.percent_fee/100) end ))))'))
            ->orderBy('transport_fees.percent_fee')
            ->first();// giá tốt nhất tìm được trong miền sau khi cộng margin và fee

        $provinceFeeMin = SupplierSupportedProvince::with('transportFee')
            ->where('supplier_id', $minPrice->supplier->id)
            ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
            ->orderBy('transport_fees.percent_fee')
            ->first(); // lấy phí vận chuyển thấp nhất của ncc cung cấp sản phẩm với giá tốt nhất
        $supportedProvince = SupplierSupportedProvince::where('supplier_id', $minPrice->supplier ? $minPrice->supplier->id : 0)->where('status', 1)->pluck('province_id');
        //kiểm tra nhà cung cấp sản phẩm có hỗ trợ cho tỉnh mua hàng ko

        if (in_array($province[0], $supportedProvince ? $supportedProvince->toArray() : [])) {
            $productFee = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01;
        } else {
            $productFee = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 + ($provinceFeeMin->transportFee ? $provinceFeeMin->transportFee->percent_fee : 0) * 0.01;
        }
        $w_margin = ($margin ? $margin->margin : 5) * 0.01;

        $product->best_price = ProductSupplier::where('product_id', $data['id'])
            ->where('product_supplier.region_id', $regions[0])
            ->where('product_supplier.state', '=', 1)
            ->where('product_supplier.supplier_id',  $minPrice->supplier->id)
            ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productFee . '/1000) * 1000))'));

        $product->import_price = ProductSupplier::where('product_id', $data['id'])
            ->where('product_supplier.region_id', $regions[0])
            ->where('product_supplier.state', '=', 1)
            ->where('product_supplier.supplier_id',  $minPrice->supplier->id)
            ->min(DB::raw('ceil(product_supplier.import_price * (' . $productFee . '-' . $w_margin . ')/1000) * 1000'));

        $product->import_price_w_margin = ProductSupplier::where('product_id', $data['id'])
            ->where('product_supplier.region_id', $regions[0])
            ->where('product_supplier.state', '=', 1)
            ->where('product_supplier.supplier_id',  $minPrice->supplier->id)
            ->min(DB::raw('ceil(product_supplier.import_price * ' . $productFee . '/1000) * 1000'));

        $product->recommended_price = ProductSupplier::where('product_id', $data['id'])
            ->where('product_supplier.region_id', $regions[0])
            ->where('product_supplier.state', '=', 1)
            ->where('product_supplier.supplier_id',  $minPrice->supplier->id)
            ->where('price_recommend', $product->best_price)
            ->min('product_supplier.price_recommend');

        return [
            'id' => $data['id'],
            'name' => html_entity_decode($data['name']),
            'price' => $product->best_price,
            'import_price' => $product->import_price,
            'import_price_w_margin' => $product->import_price_w_margin,
            'recommended_price' => $product->recommended_price ? $product->recommended_price : 0,
            'source_url' => $data['image'],
            'sku' => $data['sku']
        ];
    }
}