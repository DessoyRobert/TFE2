<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ComponentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'price'   => $this->price,
            'img_url' => $this->img_url,
            'brand'   => $this->whenLoaded('brand', fn() => [
                'id' => $this->brand->id ?? null,
                'name' => $this->brand->name ?? null,
            ]),
            'type'    => $this->whenLoaded('type', fn() => [
                'id' => $this->type->id ?? null,
                'name' => $this->type->name ?? null,
            ]),
        ];
    }
}
