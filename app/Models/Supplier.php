<?php

namespace App\Models;

use App\Jobs\PublishMessage;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use Trackable, HasUpdater;

    protected $with = ['supplierSupportedProvince', 'address'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function addresses()
    {
        return $this->hasMany(SupplierAddress::class);
    }

    public function address()
    {
        return $this->hasOne(SupplierAddress::class);
    }

    public function defaultAddress()
    {
        return $this->addresses()->find($this->default_address_id) ? : new SupplierAddress;
    }

    public function bankAccounts()
    {
        return $this->hasMany(SupplierBankAccount::class);
    }

    public function defaultBankAccount()
    {
        return $this->bankAccounts()->find($this->default_bank_account_id) ? : new SupplierBankAccount;
    }

    public function supplierSupportedProvince()
    {
        return $this->belongsToMany(Province::class, 'supplier_supported_province', 'supplier_id', 'province_id');
    }

    public function scopeHasNoProducts($query)
    {
        return $query->where('suppliers.status', true)
            ->leftJoin('product_supplier', 'suppliers.id', '=', 'product_supplier.supplier_id')
            ->whereNull('product_supplier.id');
    }

    public function addDefaultAddress($data)
    {
        $address = $this->addAddress(array_merge($data, [
            'status' => true,
        ]));

        $address->forceFill([
            'is_default' => true,
        ])->save();

        $this->forceFill([
            'default_address_id' => $address->id,
        ])->save();

        return $this;
    }

    public function addAddress($data)
    {
        $province = Province::find($data['province_id']) ? : new Province;

        $district = $province->districts()
            ->where('district_id', $data['district_id'])
            ->first() ? : new District;

        return SupplierAddress::forceCreate([
            'supplier_id' => $this->id,
            'province_id' => $data['province_id'],
            'district_id' => $data['district_id'],
            'province_name' => $province->name,
            'district_name' => $district->name,
            'address' => $data['address'],
            'addressCode' => $data['addressCode'],
            'contact_name' => $data['contact_name'],
            'contact_mobile' => $data['contact_mobile'],
            'contact_phone' => $data['contact_phone'],
            'contact_email' => $data['contact_email'],
            'status' => $data['status'],
        ]);
    }

    public function addDefaultBankAccount($data)
    {
        $bankAccount = $this->addBankAccount(array_merge($data, [
            'status' => true,
        ]));

        $bankAccount->forceFill([
            'is_default' => true,
        ])->save();

        $this->forceFill([
            'default_bank_account_id' => $bankAccount->id,
        ])->save();

        return $this;
    }

    public function addBankAccount($data)
    {
        return SupplierBankAccount::forceCreate([
            'supplier_id' => $this->id,
            'bank_account' => $data['bank_account'],
            'bank_account_name' => $data['bank_account_name'],
            'bank_name' => $data['bank_name'],
            'bank_code' => $data['bank_code'],
            'bank_branch' => $data['bank_branch'],
            'bank_province' => $data['bank_province'],
            'status' => $data['status'],
        ]);
    }

    public function addSupportedProvince($provinceId)
    {
        SupplierSupportedProvince::updateOrCreate([
            'supplier_id' => $this->id,
            'province_id' => $provinceId,
        ], [
            'status' => true,
        ]);

        return $this;
    }

    public function broadcastUpserted()
    {
        $body = [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'status' => $this->status ? 'active' : 'inactive',
            'phone' => $this->phone,
            'fax' => $this->fax,
            'email' => $this->email,
            'website' => $this->website,
            'tax_number' => $this->tax_number,
            'contactName' => $this->defaultAddress()->contact_name,
            'contactMobile' => $this->defaultAddress()->contact_mobile,
            'contactPhone' => $this->defaultAddress()->contact_phone,
            'contactEmail' => $this->defaultAddress()->contact_email,

            'createdAt' => strtotime($this->created_at),
            'addresses' => [
                'default' => [
                    'province' => $this->defaultAddress()->province_name,
                    'district' => $this->defaultAddress()->district_name,
                    'address' => $this->defaultAddress()->address,
                    'addressCode' => $this->defaultAddress()->addressCode,
                    'contactName' => $this->defaultAddress()->contact_name,
                    'contactMobile' => $this->defaultAddress()->contact_mobile,
                    'contactPhone' => $this->defaultAddress()->contact_phone,
                    'contactEmail' => $this->defaultAddress()->contact_email,
                ],
                'others' => []
            ],
            'supportedProvince' => [
                $this->defaultAddress()->province_name
            ],
            'accounts' => [
                'default' => [
                    'bankAccount' => $this->defaultBankAccount()->bank_account,
                    'bankAccountName' => $this->defaultBankAccount()->bank_account_name,
                    'bankName' => $this->defaultBankAccount()->bank_name,
                    'bankCode' => $this->defaultBankAccount()->bank_code,
                    'bankProvince' => $this->defaultBankAccount()->bank_province,
                    'bankBranch' => $this->defaultBankAccount()->bank_branch,
                ],
                'others' => []
            ]
        ];

        dispatch(new PublishMessage('teko.sale', 'sale.supplier.upsert', $body));
    }

    public function offProductsWhenInactive()
    {
        if ($this->status) {
            return false;
        }

        $user = Sentinel::getUser();

        $productOffs = DB::select("select a.product_id, a.region_id from
                (select product_id, region_id FROM `product_supplier` WHERE supplier_id = ? and state = 1 and `status` != 0 GROUP BY product_id) a
                left join (select product_id, region_id from product_supplier a left join suppliers b on a.supplier_id = b.id
                where a.state = 1
                and b.status = 1
                and a.supplier_id <> ?
                group by a.product_id, region_id) b on a.product_id = b.product_id and a.region_id = b.region_id where b.product_id is null
                 ", [$this->id, $this->id]);

        $productRegions = [];

        foreach ($productOffs as $product) {
            $productRegions[$product->product_id][] = $product->region_id;
        }

        $products = Product::whereIn('id', array_keys($productRegions))->get();

        foreach ($products as $product) {
            foreach ($productRegions[$product->id] as $regionId) {
                dispatch(new OffProductToMagento($product, 0, $user, $regionId));
                $productSupplier = ProductSupplier::where('product_id', $product->id)
                    ->where('region_id', $regionId)
                    ->where('supplier_id', $this->id)
                    ->first();
                $productSupplier->status = 0;
                $productSupplier->state = 0;
                $productSupplier->save();
            }
        }

        return true;
    }
}
