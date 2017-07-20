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

/**
 * @property  int provinceIds
 * @property  int code
 */
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
        $province = Province::whereIn('code', $this->code)->pluck('id');
        $provinceFee = TransportFee::whereIn('province_id', $province)
            ->orderBy('percent_fee')
            ->first();
        $supplierIds = SupplierSupportedProvince::whereIn('province_id', $this->provinceIds)
            ->get()
            ->pluck('supplier_id');

        $product = Product::with('manufacturer', 'category')
            ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
            ->leftJoin('product_supplier', function ($q) use ($supplierIds) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->whereIn('product_supplier.supplier_id', $supplierIds)
                    ->where('product_supplier.state', '=', 1);
            })
            ->findOrFail($data['id']);
        $regions = Province::whereIn('code', request('province_ids'))->pluck('region_id');
        $margin = MarginRegionCategory::where('category_id', $product->category_id)
            ->whereIn('region_id', $regions)->first();

        $minPrice = ProductSupplier::where('product_id', $data['id'])
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->where('product_supplier.state', '=', 1)
            ->orderBy('import_price')
            ->first();

        $provinceFeeMin = SupplierSupportedProvince::with('transportFee')
            ->where('supplier_id', $minPrice->supplier->id)
            ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
            ->orderBy('transport_fees.percent_fee')
            ->first();
        $ship_province = $province->toArray();
        if (in_array($provinceFeeMin->transportFee ? $provinceFeeMin->transportFee->province_id : 0, $ship_province )){
            $productFee = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01;
        }else{
            $productFee = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 + ($provinceFeeMin->transportFee ? $provinceFeeMin->transportFee->percent_fee : 0) * 0.01;
        }

        $product->best_price = ProductSupplier::where('product_id', $data['id'])
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->where('product_supplier.state', '=', 1)
            ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productFee . '/1000) * 1000))'));

        $product->import_price = ProductSupplier::where('product_id', $data['id'])
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->where('product_supplier.state', '=', 1)
            ->min(DB::raw('ceil(product_supplier.import_price / 1000) * 1000'));

        $product->import_price_w_margin = ProductSupplier::where('product_id', $data['id'])
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->where('product_supplier.state', '=', 1)
            ->min(DB::raw('ceil(product_supplier.import_price * ' . $productFee . '/1000) * 1000'));

        $product->recommended_price = ProductSupplier::where('product_id', $data['id'])
            ->whereIn('product_supplier.supplier_id', $supplierIds)
            ->where('product_supplier.state', '=', 1)
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