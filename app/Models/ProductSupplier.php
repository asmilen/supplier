<?php

namespace App\Models;

use DB;
use Sentinel;
use GuzzleHttp\Client;
use Datatables;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\UpdateProductPriceToMagento;

class ProductSupplier extends Model
{
    use Trackable, HasUpdater;

    protected $table = 'product_supplier';

    protected $fillable = [
        'product_id',
        'supplier_id',
        'code',
        'import_price',
        'price_recommend',
        'vat',
        'status',
        'state',
        'from_date',
        'to_date',
        'description',
        'image',
        'name',
        'quantity',
        'created_by',
        'updated_by',
    ];


    public static $STATE_HET_HANG = 0;
    public static $STATE_CON_HANG = 1;
    public static $STATE_DAT_HANG = 2;

    public static $STATUS_CHO_DUYET = 0;
    public static $STATUS_HET_HANG = 1;
    public static $STATUS_UU_TIEN_LAY_HANG = 2;
    public static $STATUS_YEU_CAU_UU_TIEN_LAY_HANG = 3;
    public static $STATUS_KHONG_UU_TIEN_LAY_HANG = 4;


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            dispatch(new UpdateProductPriceToMagento($model->product));
        });
    }

    public static function getListByRegion($regionId)
    {
        return collect(DB::select('
                SELECT ps.product_id, p.sku, ps.import_price, ps.state, ps.created_at, ps.updated_at FROM product_supplier AS ps
                INNER JOIN products AS p ON ps.product_id = p.id
                WHERE supplier_id IN (
                    SELECT supplier_id FROM supplier_supported_province
                    WHERE province_id IN (
                        SELECT id FROM provinces WHERE region_id = ' . $regionId . '
                    )
                )
                ORDER BY updated_at DESC, created_at DESC
        '))->groupBy('product_id');
    }

    public function scopeCanManage($query)
    {
        $user = Sentinel::getUser();

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $regionIds = collect(DB::select('select region_id from user_supported_province where supported_id = ?', [$user->id]))
            ->pluck('region_id')
            ->toArray();
        return $query->whereIn('region_id', $regionIds);
    }

    public function offProductToMagento()
    {
        $product = Product::where('id', $this->product_id)->pluck('sku');

        $post_data = [
            'data' => [
                'status' => 0,
                'products' => $product
            ]
        ];

        $this->status = 0;
        $this->state = 2;
        $this->save();

        $response = $this->callApi($post_data);

        LogOffSupplier::create([
            'supplier_id' => $this ? $this->supplier_id : 0,
            'type' => 'OFF',
            'post_data' => json_encode($post_data),
            'response' => json_encode($response)
        ]);
    }
    public function onProductToMagento()
    {
        $product = Product::where('id', $this->product_id)->pluck('sku');

        $post_data = [
            'data' => [
                'status' => 1,
                'products' => $product
            ]
        ];

        $this->status = 1;
        $this->state = 1;
        $this->save();

        $response = $this->callApi($post_data);

        LogOffSupplier::create([
            'supplier_id' => $this ? $this->supplier_id : 0,
            'type' => 'ON',
            'post_data' => json_encode($post_data),
            'response' => json_encode($response)
        ]);
    }

    private function callApi($data)
    {
        $client = new Client(['base_uri' => env('OFF_PRODUCT_URL_BASE'), 'verify' => false]);
        /**
         * @var \GuzzleHttp\Psr7\Response $result
         */
        $result = $client->post(env('OFF_PRODUCT_URL'), [
            'body' => json_encode($data),
        ]);

        if (null === $decodedResult = json_decode($result->getBody()->getContents(), true)) {
            return array('errorMessage' => 'Could not decode json');
        }
        return $decodedResult;
    }
}
