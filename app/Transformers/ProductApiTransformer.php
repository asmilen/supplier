<?php
namespace App\Transformers;

use \League\Fractal\TransformerAbstract;

class ProductApiTransformer extends TransformerAbstract
{
    /**
     * @param array $data
     * @return array
     */
    public function transform($data)
    {
        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'price' => $data['price'],
            'import_price' => $data['import_price'],
            'source_url' => $data['image'],
            'sku' => $data['sku'],
            'recommended_price' => $data['recommended_price']
        ];
    }
}