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

        $attribute->addOption((new AttributeOption)->forceFill([
            'value' => request('value'),
        ]));
    }

    public function update(Attribute $attribute, AttributeOption $option)
    {
        $this->validate(request(), [
            'value' => 'required|max:255',
        ]);

        $option->forceFill(['value' => request('value')])->save();
    }
}
