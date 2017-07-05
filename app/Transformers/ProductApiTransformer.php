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
            'name' => html_entity_decode($data['name']),
            'price' => $data['price'],
            'import_price' => $data['import_price'],
            'import_price_w_margin' => $data['import_price_w_margin'],
            'recommended_price' => $data['recommended_price'],
            'source_url' => $data['image'],
            'sku' => $data['sku']
        ];
    }
}