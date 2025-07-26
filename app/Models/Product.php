<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name_uz',
        'name_ru',
        'name_en',
        'name_tr',
        'description_uz',
        'description_ru',
        'description_en',
        'description_tr',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];
}
