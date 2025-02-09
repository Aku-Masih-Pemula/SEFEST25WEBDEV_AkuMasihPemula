<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $formattedDate = $this->created_at ? Carbon::parse($this->created_at)->format('j F Y') : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'date' => $formattedDate,
            'status' => $this->status,
            'seller' => $this->whenLoaded('seller', function() {
                return [
                    'name' => $this->seller->name,
                    'email' => $this->seller->email,
                    'phone' => $this->seller->phone,
                ];
            }),
            'category' => $this->whenLoaded('category', function() {
                return [
                    'name' => $this->category->name
                ];
            }),
            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->path ? asset('storage/' . $image->path) : null
                    ];
                });
            })
        ];
    }
}
