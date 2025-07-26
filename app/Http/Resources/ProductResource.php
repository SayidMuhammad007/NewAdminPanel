<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    // $wrap ni olib tashlash, chunki biz controller da struktura hosil qilamiz
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => [
                'uz' => $this->name_uz,
                'ru' => $this->name_ru,
                'en' => $this->name_en,
                'tr' => $this->name_tr,
            ],
            'description' => [
                'uz' => $this->description_uz,
                'ru' => $this->description_ru,
                'en' => $this->description_en,
                'tr' => $this->description_tr,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'images' => $this->images ? array_map(function($image) {
                return asset('storage/' . $image);
            }, $this->images) : [],
        ];
    }
}