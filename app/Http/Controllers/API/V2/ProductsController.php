<?php

namespace App\Http\Controllers\API\V2;

use DB;
use Validator;
use Datatables;
use App\Models\Product;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\TransportFee;
use App\Models\ProductSupplier;
use App\Http\Controllers\Controller;
use App\Models\MarginRegionCategory;
use App\Models\SupplierSupportedProvince;

class ProductsController extends Controller
{
    public function getListWithPrices()
    {
        $this->validate(request(), [
            'ids' => 'required',
            'province_id' => 'required',
        ]);

        $regionId = Province::where('code', request('province_id'))->pluck('region_id')->first();

        if (! $regionId) {
            return response([
                'error' => 'Mã tỉnh thành không tồn tại.'
            ], 422);
        }

        $products = $this->getProductsListByChannelAndRegion(
            explode(',', request('ids', [])),
            $regionId,
            request('channels', [2])
        );

        foreach ($products as $product) {
            $margin = MarginRegionCategory::where('category_id', $product->category_id)
                ->where('region_id', $regionId)->first(); // tính margin của category sản phẩm

            $marginValue = ($margin ? 1 + 0.01 * $margin->margin : 1.05); // giá trị của margin

            $province = Province::where('code', request('province_id'))->pluck('id'); // tìm tỉnh mua hàng

            $provinceFee = TransportFee::where('province_id', $province ? $province[0] : 0)->first();// phí ship của tỉnh mua hàng

            $feeValue = ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01; //giá trị của phí ship của tỉnh mua hàng

            $provinceIds = Province::where('region_id', $regionId)->pluck('id');
            $provinceFeeMax = TransportFee::whereIn('province_id', $provinceIds)->orderBy('percent_fee', 'DESC')->first();

            $minPrice = ProductSupplier::where('product_id', $product->id)
                ->where('product_supplier.region_id', $regionId)
                ->leftJoin('supplier_supported_province', 'product_supplier.supplier_id', '=', 'supplier_supported_province.supplier_id')
                ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
                ->where('product_supplier.state', '=', 1)
                ->orderBy(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, product_supplier.import_price * (' .
                    $marginValue . '+' . $feeValue . '+' . '(case when supplier_supported_province.province_id = ' . $province[0] . ' then 0 else if(transport_fees.percent_fee is null, 0,transport_fees.percent_fee/100) end ))))'))
                ->orderBy('transport_fees.percent_fee')
                ->first(); // giá tốt nhất tìm được trong miền sau khi cộng margin và fee

            if ($minPrice) {
                $provinceFeeMin = SupplierSupportedProvince::with('transportFee')
                    ->where('supplier_id', $minPrice ? $minPrice->supplier->id : 0)
                    ->leftJoin('transport_fees', 'transport_fees.province_id', '=', 'supplier_supported_province.province_id')
                    ->orderBy('transport_fees.percent_fee')
                    ->first(); // lấy phí vận chuyển thấp nhất của ncc cung cấp sản phẩm với giá tốt nhất

                $supportedProvince = SupplierSupportedProvince::where('supplier_id', $minPrice->supplier ? $minPrice->supplier->id : 0)->pluck('province_id');
                //kiểm tra nhà cung cấp sản phẩm có hỗ trợ cho tỉnh mua hàng ko

                if (in_array($province[0], $supportedProvince ? $supportedProvince->toArray() : [])) {
                    $productMargin = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01;
                } else {
                    $productMargin = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 + ($provinceFeeMin->transportFee ? $provinceFeeMin->transportFee->percent_fee : 0) * 0.01;
                }
                $productFeeMax = 1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFeeMax ? $provinceFeeMax->percent_fee : 0) * 0.01 * 2;
                $w_margin = ($margin ? $margin->margin : 5) * 0.01;

                $product->official_price = ceil(rtrim(rtrim(sprintf('%f', $minPrice->import_price * $productFeeMax / 1000), '0'), '.')) * 1000;

                $product->best_price = ProductSupplier::where('product_id', $product->id)
                    ->where('product_supplier.region_id', $regionId)
                    ->where('product_supplier.state', '=', 1)
                    ->where('product_supplier.supplier_id', $minPrice->supplier->id)
                    ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000))'));

                $product->import_price = ProductSupplier::where('product_id', $product->id)
                    ->where('product_supplier.region_id', $regionId)
                    ->where('product_supplier.state', '=', 1)
                    ->where('product_supplier.supplier_id', $minPrice->supplier->id)
                    ->min(DB::raw('ceil(product_supplier.import_price * (' . $productMargin . '-' . $w_margin . ')/1000) * 1000'));

                $product->import_price_w_margin = ProductSupplier::where('product_id', $product->id)
                    ->where('product_supplier.region_id', $regionId)
                    ->where('product_supplier.state', '=', 1)
                    ->where('product_supplier.supplier_id', $minPrice->supplier->id)
                    ->min(DB::raw('ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000'));

                $product->recommended_price = ProductSupplier::where('product_id', $product->id)
                    ->where('product_supplier.region_id', $regionId)
                    ->where('product_supplier.state', '=', 1)
                    ->where('price_recommend', $product->best_price)
                    ->where('product_supplier.supplier_id', $minPrice->supplier->id)
                    ->min('product_supplier.price_recommend');

                if ($product->recommended_price == $product->best_price) {
                    $suppliers = ProductSupplier::where('price_recommend', $product->best_price)
                        ->where('product_id', $product->id)
                        ->leftJoin('suppliers', 'product_supplier.supplier_id', '=', 'suppliers.id')
                        ->where('product_supplier.region_id', $regionId)
                        ->pluck('supplier_id');
                    $supplier = Supplier::whereIn('id', $suppliers ? $suppliers : 0)
                        ->where('status', 1)
                        ->get();
                    $province = SupplierSupportedProvince::whereIn('supplier_id', $suppliers ? $suppliers : 0)
                        ->leftJoin('provinces', 'supplier_supported_province.province_id', '=', 'provinces.id')
                        ->get();
                } else {
                    $supplier = Supplier::where('id', $provinceFeeMin ? $provinceFeeMin->supplier_id : 0)
                        ->get();
                    $province = Province::where('id', $provinceFeeMin ? $provinceFeeMin->province_id : 0)
                        ->get();
                }
                for ($i = 0; $i < $supplier->count(); $i++) {
                    $product->suppliers = array_merge([[
                        'id' => isset($supplier[$i]) ? $supplier[$i]->id : null,
                        'name' => isset($supplier[$i]) ? $supplier[$i]->name : null,
                        'import_price' => ProductSupplier::where('product_id', $product->id)
                            ->where('product_supplier.supplier_id', $supplier[$i]->id)
                            ->where('product_supplier.state', '=', 1)
                            ->min(DB::raw('ceil(product_supplier.import_price / 1000) * 1000')),
                        'province_name' => isset($province[$i]) ? $province[$i]->name : null,
                        'province_code' => isset($province[$i]) ? $province[$i]->code : null
                    ]], is_array($product->suppliers) ? $product->suppliers : []);
                }
            }
        }

        return $products;
    }

    protected function getProductsListByChannelAndRegion($productIds, $regionId, $channels)
    {
        return Product::with('manufacturer', 'category')
            ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`,  `products`.`description`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`, `product_supplier`.`import_price`"))
            ->leftJoin('product_supplier', function ($q) use ($regionId) {
                $q->on('product_supplier.product_id', '=', 'products.id')
                    ->where('product_supplier.region_id', $regionId)
                    ->where('product_supplier.state', '=', 1);
            })
            ->where(function ($q) use ($channels) {
                foreach ($channels as $key => $channel) {
                    $q->orWhere('products.channel', 'like', '%' . $channel . '%');
                }
            })
            ->whereIn('products.id', $productIds)
            ->groupBy('products.id')
            ->get();
    }
}
