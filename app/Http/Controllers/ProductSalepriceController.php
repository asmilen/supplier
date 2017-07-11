<?php

namespace App\Http\Controllers;

use App\Models\ProductSupplier;
use App\Models\SalepriceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Validator;
use App\Models\Product;
use App\Models\Saleprice;
use DB;
use App\Models\Province;
use App\Models\SupplierSupportedProvince;
use App\Models\MarginRegionCategory;
use GuzzleHttp\Client;
use Sentinel;

class ProductSalepriceController extends Controller
{
    public function show(Product $product)
    {
        $productSuppliers = ProductSupplier::where('product_id', $product->id)
            ->orderBy('import_price')
            ->take(5)
            ->get();

        $nowSalePrices = Saleprice::whereIn('id',
            Saleprice::select(DB::raw('MAX(id) as id_p'))
                ->where('product_id', $product->id)
                ->groupBy('store_id', 'region_id')
                ->get()
        )->get()->sortBy('region_id')->groupBy('region_id');

        $productMarket = DB::table('product_marketprice_best')->where('product_id', $product->id)->first();

        return view('products.saleprice.show', compact('product', 'productSuppliers', 'nowSalePrices', 'productMarket'));
    }

    public function update(Product $product)
    {
        Validator::make(request()->all(), [
            'stores.*' => 'required',
        ])->after(function ($validator) use ($product) {
            if (request('price') <= 0) {
                $validator->errors()->add('price', 'Giá bán phải > 0.');
            }
            if (!in_array(true, request('stores'))) {
                $validator->errors()->add('stores', 'Bạn phải chọn ít nhất 1 store.');
            }
            if (!in_array(true, request('regions'))) {
                $validator->errors()->add('regions', 'Bạn phải chọn ít nhất 1 miền.');
            }
            foreach (request('regions') as $regionId => $flagRegion) {
                if ($flagRegion) {
                    $provinceIds = Province::where('region_id', $regionId)->pluck('id');

                    $supplierIds = SupplierSupportedProvince::whereIn('province_id', $provinceIds)
                        ->get()
                        ->pluck('supplier_id');

                    $margin = MarginRegionCategory::where('category_id', $product->category_id)
                        ->where('region_id', $regionId)->first();

                    if ($margin) {
                        $productMargin = 1 + 0.01 * $margin->margin;
                    } else {
                        $productMargin = 1.05;
                    }

                    $minPrice = ProductSupplier::where('product_id', $product->id)
                        ->whereIn('product_supplier.supplier_id', $supplierIds)
                        ->min(DB::raw('(if(product_supplier.price_recommend > 0, product_supplier.price_recommend, ceil(product_supplier.import_price * ' . $productMargin . '/1000) * 1000))'));
                    if (request('price') < $minPrice) {
                        $validator->errors()->add('price', 'Giá bán không hợp lệ cho ' . config('teko.regions')[$regionId]);
                    }
                }
            }
        })->validate();

        $id = [];
        $storeName = [];
        $regionName = [];
        $date = Carbon::now('Asia/Ho_Chi_Minh')->subMinute(120)->getTimestamp();
        $user_id = Sentinel::getUser()->id;

        foreach (request('stores') as $storeId => $flagStore) {
            if ($flagStore) {
                array_push($storeName, $storeId);
                foreach (request('regions') as $regionId => $flagRegion) {
                    if ($flagRegion) {
                        try {
                            $region_name = config('teko.regions')[$regionId];
                            array_push($regionName, $region_name);

                            $product->addSaleprice([
                                'store_id' => $storeId,
                                'region_id' => $regionId,
                                'price' => request('price'),
                            ]);
                            $change_info = [
                                'user_id' => $user_id,
                                "product_id" => $product->id,
                                'price' => request('price'),
                                'store' => $storeId,
                                'region' => $regionId,
                                'status' => 0
                            ];
                            $log = SalepriceLog::create($change_info);
                            $log->save();

                            array_push($id, $log->id);

                        } catch (\Exception $e) {
                            return response()->json([
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }
        }
        $data = [
            "storeIds" => array_unique($storeName),
            "regionNames" => array_unique($regionName),
            "productId" => $product->id,
            "price" => request('price'),
            "createdAt" => $date
        ];
        $response = $this->callApi($data);
        if ($response['status'] == 'success') {
            for ($i = 0; $i < count($id); $i++) {
                $sales = SalepriceLog::where('id', $id[$i])->first();
                $sales['detail'] = json_encode($response['detail']);
                $sales['status'] = 1;
                $sales->save();
            }
        }
        return $product;
    }

    private function callApi($data)
    {
        $client = new Client(['base_uri' => env('UPDATE_PRICE_URL_BASE')]);
        /**
         * @var \GuzzleHttp\Psr7\Response $result
         */
        $result = $client->request('POST', env('UPDATE_PRICE_URL'), [
            'body' => json_encode($data),
        ]);

        if (null === $decodedResult = json_decode($result->getBody()->getContents(), true)) {
            return array('errorMessage' => 'Could not decode json');
        }
        return $decodedResult;
    }
}
