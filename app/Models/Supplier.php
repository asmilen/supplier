<?php

namespace App\Models;

use Datatables;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use Trackable, HasUpdater;

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function addresses()
    {
        return $this->hasMany(SupplierAddress::class);
    }

    public function address()
    {
        return $this->hasOne(SupplierAddress::class);
    }

    public function suppliers_supported_provinces()
    {
        return $this->belongsToMany(Province::class, 'supplier_supported_province', 'supplier_id', 'province_id');
    }

    public function supplier_bank()
    {
        return $this->belongsTo(SupplierBankAccount::class, 'id', 'supplier_id');
    }

    public static function getDatatables()
    {
        $model = static::select([
            'id', 'name', 'full_name', 'code', 'tax_number', 'status', 'type', 'sup_type', 'price_active_time'
        ])->with('addresses', 'address', 'suppliers_supported_provinces');

        return Datatables::eloquent($model)
            ->filter(function ($query) {
                if (request()->has('keyword')) {
                    $query->where('name', 'like', '%' . request('keyword') . '%');
                }

                if (request()->has('typeId')) {
                    $query->where('type', request('typeId'));
                }

                if (request()->has('province')) {
                    $query->where('type', request('typeId'));
                }

                if (request('status') == 'active') {
                    $query->where('status', true);
                } elseif (request('status') == 'inactive') {
                    $query->where('status', false);
                }
            })
            ->editColumn('province', function ($model) {
                return $model->suppliers_supported_provinces ? $model->suppliers_supported_provinces->first()['name'] : '';
            })
            ->editColumn('address', function ($model) {
                return $model->address ? $model->address->address : '';
            })
            ->editColumn('info_person', function ($model) {
                $string = $model->address ? $model->address->contact_name : '';
                $string .= ' - ';
                $string .= $model->address ? $model->address->contact_phone : '';
                return $string;
            })
            ->editColumn('price_active_time', function ($model) {
                return ($model->price_active_time / 24) . ' ngày';
            })
            ->editColumn('sup_type', function ($model) {
                if ($model->sup_type === 1) {
                    return 'Ký gửi';
                } else
                    return 'Hàng mua';
            })
            ->editColumn('status', 'products.datatables.status')
            ->addColumn('action', 'suppliers.datatables.action')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function scopeHasNoProducts($query)
    {
        return $query->where('suppliers.status', true)
            ->leftJoin('product_supplier', 'suppliers.id', '=', 'product_supplier.supplier_id')
            ->whereNull('product_supplier.id');
    }

    public function offProductToMagento()
    {
        $product_ids = ProductSupplier::where('supplier_id', $this->id)
            ->where('state', 1)
            ->where('status', '!=', 0)
            ->pluck('product_id');
        $product_list = array();

        for ($i = 0; $i < $product_ids->count(); $i++) {
            $suppliers = ProductSupplier::where('product_supplier.product_id', $product_ids[$i])
                ->leftJoin('suppliers', 'product_supplier.supplier_id', 'suppliers.id')
                ->where('product_supplier.state', 1)
                ->where('suppliers.status', 1)
                ->where('suppliers.id', '!=', $this->id)
                ->get();
            if ($suppliers->count() == 0) {
                array_push($product_list, $product_ids[$i]);

                $product_supplier = ProductSupplier::where('product_supplier.product_id', $product_ids[$i])
                    ->where('product_supplier.state', 1)
                    ->get();

                $product_supplier[0]->status = 0;

                $product_supplier[0]->save();

            }
        }

        $sku = Product::whereIn('id', array_unique($product_list))->pluck('sku');

        $post_data = [
            'data' => [
                'status' => 1,
                'products' => $sku
            ]
        ];
        $response = $this->callApi($post_data);
        LogOffSupplier::create([
            'supplier_id' => $this ? $this->id : 0,
            'type' => 'OFF',
            'post_data' => json_encode($post_data),
            'response' => json_encode($response)
        ]);
    }

    public function onProductToMagento()
    {
        $product_ids = ProductSupplier::where('supplier_id', $this->id)
            ->where('state', 1)
            ->where('status', 0)
            ->pluck('product_id');
        $product_list = array();

        for ($i = 0; $i < $product_ids->count(); $i++) {
            if ($product_ids->count() > 0) {
                array_push($product_list, $product_ids[$i]);

                $product_supplier = ProductSupplier::where('product_supplier.product_id', $product_ids[$i])
                    ->where('product_supplier.state', 1)
                    ->get();

                $product_supplier[0]->status = 1;

                $product_supplier[0]->save();

            }
        }

        $sku = Product::whereIn('id', array_unique($product_list))->pluck('sku');

        $post_data = [
            'data' => [
                'status' => 1,
                'products' => $sku
            ]
        ];
        $response = $this->callApi($post_data);
        LogOffSupplier::create([
            'supplier_id' => $this ? $this->id : 0,
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
