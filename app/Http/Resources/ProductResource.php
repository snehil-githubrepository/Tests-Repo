<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
           'product_id' => $this->id,
           'user_id' => $this->user_id,
           'product_name' => $this->product_name,
           'product_price' => $this->product_price,
           'product_description' => $this->product_description
        ];
    }
}
