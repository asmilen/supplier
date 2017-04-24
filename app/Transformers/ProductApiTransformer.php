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
            'source_url' => $data['image']
        ];
    }
}