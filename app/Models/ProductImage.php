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

    protected $appends = ['image_url'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getImageUrlAttribute()
    {
        return env('APP_URL') . '/storage/images/' . $this->image_path;
    }

    protected $casts = [
        'image_order' => 'integer'
    ];

    protected $table = 'product_images';
}
