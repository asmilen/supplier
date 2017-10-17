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

    public function __construct($provinceIds, $code, $provinceFee, $feeValue)
    {
        $this->provinceIds = $provinceIds;
        $this->code = $code;
        $this->provinceFee = $provinceFee;
        $this->feeValue = $feeValue;
    }

    /**
     * @param array $data
     * @return array
     */
    public function transform($data)
    {
        $regions = Province::whereIn('code', request('province_ids'))->pluck('region_id'); // tìm miền của tỉnh mua hàng

        $margin = MarginRegionCategory::where('category_id', $data['category_id'])
            ->whereIn('region_id', $regions)->first(); // tính margin của category sản phẩm

        $marginValue = ($margin ? 1 + 0.01 * $margin->margin : 1.05); // giá trị của margin

        $province = Province::whereIn('code', request('province_ids'))->pluck('id'); // tìm tỉnh mua hàng

        $provinceFee = $this->provinceFee;// phí ship của tỉnh mua hàng

        $feeValue = $this->feeValue; //giá trị của phí ship của tỉnh mua hàng

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
            ->where('supplier_id', $minPrice->supplier_id)
            ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
            ->orderBy('transport_fees.percent_fee')
            ->first(); // lấy phí vận chuyển thấp nhất của ncc cung cấp sản phẩm với giá tốt nhất

        $supportedProvince = SupplierSupportedProvince::where('supplier_id', $minPrice ? $minPrice->supplier_id : 0)->pluck('province_id');
        //kiểm tra nhà cung cấp sản phẩm có hỗ trợ cho tỉnh mua hàng ko

        $provinceFeeMax = TransportFee::whereIn('province_id', $this->provinceIds)->orderBy('percent_fee', 'DESC')->first();

        if (in_array($province[0], $supportedProvince ? $supportedProvince->toArray() : [])) {
            $productFee = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01;
        } else {
            $productFee = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 + ($provinceFeeMin->transportFee ? $provinceFeeMin->transportFee->percent_fee : 0) * 0.01;
        }
        $productFeeMax = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFeeMax ? $provinceFeeMax->percent_fee : 0) * 0.01 * 2;
        $w_margin = ($margin ? $margin->margin : 5) * 0.01;

        $product = ProductSupplier::select('product_supplier.import_price', 'products.description')
            ->where('product_supplier.product_id', $data['id'])
            ->where('product_supplier.region_id', $regions[0])
            ->where('product_supplier.state', '=', 1)
            ->where('product_supplier.supplier_id',  $minPrice->supplier_id)
            ->leftJoin('products', 'product_supplier.product_id', 'products.id')
            ->first();

        $import_price = ceil(rtrim(rtrim(sprintf('%f', $product->import_price * ($productFee - $w_margin) / 1000), '0'), '.')) * 1000;
        $import_price_w_margin = ceil(rtrim(rtrim(sprintf('%f', $product->import_price * $productFee / 1000), '0'), '.')) * 1000;
        $official_price = ceil(rtrim(rtrim(sprintf('%f', $product->import_price * $productFeeMax / 1000), '0'), '.')) * 1000;

        return [
            'id' => $data['id'],
            'name' => html_entity_decode($data['name']),
            'description' => $product->description,
            'price' => (Int)$data['price'],
            'import_price' => $import_price,
            'import_price_w_margin' => $import_price_w_margin,
            'recommended_price' =>  $data['recommended_price'] ? (Int)$data['recommended_price'] : 0,
            'official_price' => $official_price,
            'source_url' => $data['image'],
            'sku' => $data['sku']
        ];
    }
}