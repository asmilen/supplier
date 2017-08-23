<?php

namespace App\Jobs;

use App\Models\MarginRegionCategory;
use App\Models\PostPriceToMgtLog;
use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\Province;
use App\Models\SupplierSupportedProvince;
use App\Models\TransportFee;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Sentinel;
use DB;

class UpdatePriceToMagento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user_id, $check;

    /**
     * Create a new job instance.
     *
     * @param int $user_id
     * @param int $check
     */
    public function __construct($user_id, $check)
    {
        $this->user_id = $user_id;
        $this->check = $check;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->check == 0) {
//            $this->updatePrice();
            $count = ProductSupplier::where('supplier_id', '!=', 0)
                ->where('state', 1)->count();
            for ($i = 0; $i < $count; $i = $i + 100) {
                dispatch(new PostPriceToMagento($this->user_id, $i));
            }
        } else {
            $this->postPrice();
        }
    }

    private function callApi($data)
    {
        $client = new Client(['base_uri' => env('UPDATE_PRICE_URL_BASE'), 'verify' => false]);
        /**
         * @var \GuzzleHttp\Psr7\Response $result
         */
        $result = $client->post(env('UPDATE_PRICE_TO_MAGENTO_URL'), [
            'body' => json_encode($data),
        ]);

        if (null === $decodedResult = json_decode($result->getBody()->getContents(), true)) {
            return array('errorMessage' => 'Could not decode json');
        }
        return $decodedResult;
    }

    public function updatePrice()
    {
        ProductSupplier::where('supplier_id', '!=', 0)
            ->where('state', 1)
            ->chunk(10, function ($products) {
                foreach ($products as $product) {
                    $detail = [
                        $product['attributes']
                    ];
                    $region = ProductSupplier::where('product_supplier.id', $product->id)
                        ->leftJoin('supplier_supported_province', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
                        ->leftJoin('provinces', 'provinces.id', '=', 'supplier_supported_province.province_id')
                        ->first();
                    $province_id = Province::where('region_id', $region->region_id)->pluck('id');
                    $provinceFee = TransportFee::whereIn('province_id', $province_id)->orderBy('percent_fee', 'DESC')->first();
                    /**
                     * @var array $supplierIds
                     */
                    $supplierIds = SupplierSupportedProvince::whereIn('province_id', $province_id)
                        ->get()
                        ->pluck('supplier_id');

                    $product = Product::with('manufacturer', 'category')
                        ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
                        ->leftJoin('product_supplier', function ($q) use ($supplierIds) {
                            $q->on('product_supplier.product_id', '=', 'products.id')
                                ->whereIn('product_supplier.supplier_id', $supplierIds)
                                ->where('product_supplier.state', '=', 1);
                        })
                        ->findOrFail($product->product_id);
                    if ($product) {
                        $margin = MarginRegionCategory::where('category_id', $product->category_id)
                            ->where('region_id', $region->region_id)->first();

                        $importPrice = ProductSupplier::where('product_id', $product->id)
                            ->whereIn('product_supplier.supplier_id', $supplierIds)
                            ->where('product_supplier.state', '=', 1)
                            ->orderBy('product_supplier.import_price')
                            ->first();

                        $import_price = ceil(($importPrice ? $importPrice->import_price : 0) * (1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 * 2) / 1000) * 1000;
                        $minPrice = ProductSupplier::where('product_id', $product->id)
                            ->whereIn('product_supplier.supplier_id', $supplierIds)
                            ->where('product_supplier.state', '=', 1)
                            ->select(DB::raw(" * , case when `product_supplier`.`price_recommend` > 0
                        and `product_supplier`.`price_recommend` <  $import_price then `product_supplier`.`price_recommend` else $import_price end as min_price"))
                            ->orderBy('min_price')
                            ->first();

                        if ($minPrice) {
                            $post_data = [
                                'data' => [
                                    [
                                        'region_id' => $region ? $region->region_id : 0,
                                        'sku' => $product ? $product->sku : 0,
                                        'price' => $minPrice ? $minPrice->min_price : 0
                                    ]
                                ]
                            ];

                            $checkLog = PostPriceToMgtLog::where('post_data', json_encode($post_data))->first();
                            if (!$checkLog) {
                                $response = $this->callApi($post_data);

                                PostPriceToMgtLog::create([
                                    'user_id' => $this->user_id,
                                    'product_id' => $product->id,
                                    'detail' => json_encode($detail[0]),
                                    'post_data' => json_encode($post_data),
                                    'response' => json_encode($response)
                                ]);
                            }
                        }
                    }
                }
            });
    }

    public function postPrice()
    {
        ProductSupplier::where('supplier_id', '!=', 0)
            ->where('id', $this->check)
            ->where('state', 1)
            ->chunk(10, function ($products) {
                foreach ($products as $product) {
                    $detail = [
                        $product['attributes']
                    ];
                    $region = ProductSupplier::where('product_supplier.id', $product->id)
                        ->leftJoin('supplier_supported_province', 'supplier_supported_province.supplier_id', '=', 'product_supplier.supplier_id')
                        ->leftJoin('provinces', 'provinces.id', '=', 'supplier_supported_province.province_id')
                        ->first();
                    $province_id = Province::where('region_id', $region->region_id)->pluck('id');
                    $provinceFee = TransportFee::whereIn('province_id', $province_id)->orderBy('percent_fee', 'DESC')->first();
                    /**
                     * @var array $supplierIds
                     */
                    $supplierIds = SupplierSupportedProvince::whereIn('province_id', $province_id)
                        ->get()
                        ->pluck('supplier_id');

                    $product = Product::with('manufacturer', 'category')
                        ->select(DB::raw("`products`.`id`, `products`.`name` , `products`.`sku`, `products`.`image` as `source_url`, `products`.`manufacturer_id`, `products`.`category_id`, `product_supplier`.`quantity`"))
                        ->leftJoin('product_supplier', function ($q) use ($supplierIds) {
                            $q->on('product_supplier.product_id', '=', 'products.id')
                                ->whereIn('product_supplier.supplier_id', $supplierIds)
                                ->where('product_supplier.state', '=', 1);
                        })
                        ->findOrFail($product->product_id);
                    if ($product) {
                        $margin = MarginRegionCategory::where('category_id', $product->category_id)
                            ->where('region_id', $region->region_id)->first();

                        $importPrice = ProductSupplier::where('product_id', $product->id)
                            ->whereIn('product_supplier.supplier_id', $supplierIds)
                            ->where('product_supplier.state', '=', 1)
                            ->orderBy('product_supplier.import_price')
                            ->first();

                        $import_price = ceil(($importPrice ? $importPrice->import_price : 0) * (1 + ($margin ? $margin->margin : 5) * 0.01 + ($provinceFee ? $provinceFee->percent_fee : 0) * 0.01 * 2) / 1000) * 1000;
                        $minPrice = ProductSupplier::where('product_id', $product->id)
                            ->whereIn('product_supplier.supplier_id', $supplierIds)
                            ->where('product_supplier.state', '=', 1)
                            ->select(DB::raw(" * , case when `product_supplier`.`price_recommend` > 0
                        and `product_supplier`.`price_recommend` <  $import_price then `product_supplier`.`price_recommend` else $import_price end as min_price"))
                            ->orderBy('min_price')
                            ->first();

                        if ($minPrice) {
                            $post_data = [
                                'data' => [
                                    [
                                        'region_id' => $region ? $region->region_id : 0,
                                        'sku' => $product ? $product->sku : 0,
                                        'price' => $minPrice ? $minPrice->min_price : 0
                                    ]
                                ]
                            ];

                            $response = $this->callApi($post_data);

                            PostPriceToMgtLog::create([
                                'user_id' => $this->user_id,
                                'product_id' => $product->id,
                                'detail' => json_encode($detail[0]),
                                'post_data' => json_encode($post_data),
                                'response' => json_encode($response)
                            ]);
                        }
                    }
                }
            });
    }
}
