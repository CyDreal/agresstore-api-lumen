<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// /**
//  * @property int $id
//  * @property string $title
//  * @property string|null $description
//  * @property string|null $category
//  * @property float $price
//  * @property float $discount
//  * @property int $main_stock
//  * @property int $weight
//  * @property string $status
//  * @property bool $has_variants
//  * @property int $purchase_count
//  * @property int $view_count
//  * @property \Carbon\Carbon $created_at
//  * @property \Carbon\Carbon $updated_at
//  */

class Product extends Model
{


    protected $fillable = [
        'product_name',
        'description',
        'category',
        'price',
        // 'discount',
        'stock',
        'weight',
        'status',
        // 'has_variants',
        'purchased_quantity',
        'view_count'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('image_order');
    }

    protected $casts = [
        // 'has_variants' => 'boolean',
        'price' => 'float',
        // 'discount' => 'float',
        'stock' => 'integer',
        'weight' => 'integer',
        'purchased_quantity' => 'integer',
        'view_count' => 'integer'
    ];

    protected $table = 'products';
}
