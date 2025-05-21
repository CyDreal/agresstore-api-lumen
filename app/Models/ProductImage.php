<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'image_order'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    protected $casts = [
        'image_order' => 'integer'
    ];

    protected $table = 'product_images';
}
