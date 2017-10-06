<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeOption;

class AttributeOptionsController extends Controller
{
    public function index(Attribute $attribute)
    {
        return $attribute->options;
    }

    public function store(Attribute $attribute)
    {
        $this->validate(request(), [
            'value' => 'required|max:255',
        ]);

        if ($attribute->hasValue(request('value'))) {
            return response([
                'value' => 'Giá trị thuộc tính đã tồn tại.'
            ], 422);
        }

        $attribute->addOption((new AttributeOption)->forceFill([
            'value' => request('value'),
        ]));
    }

    public function update(Attribute $attribute, AttributeOption $option)
    {
        $this->validate(request(), [
            'value' => 'required|max:255',
        ]);

        if ($attribute->hasValue(request('value'), $option->id)) {
            return response([
                'value' => 'Giá trị thuộc tính đã tồn tại.'
            ], 422);
        }

        $option->forceFill(['value' => request('value')])->save();
    }
}
