<?php

namespace App\Http\Controllers\API;

use App\Models\Province;
use App\Models\TransportFee;
use App\Jobs\PublishMessage;
use App\Http\Controllers\Controller;

class ProvinceTransportFeeController extends Controller
{
    public function update(Province $province)
    {
        $this->validate(request(), [
            'percent_fee' => 'required|numeric|min:0',
        ]);

        $transportFee = TransportFee::where('province_id', $province->id)->first();

        if (! $transportFee) {
            $transportFee = (new TransportFee)->forceFill([
                'province_id' => $province->id,
            ]);
        }

        $transportFee->forceFill([
            'percent_fee' => request('percent_fee'),
        ])->save();

        dispatch(new PublishMessage('teko.sale', 'sale.percentShippingFee.upsert', json_encode([
            'province' => $transportFee->province->name,
            'addressCode' => $transportFee->province->code,
            'fee' => $transportFee->percent_fee,
            'createdAt' => $transportFee->updated_at->timestamp,
        ])));

        return $transportFee;
    }
}
