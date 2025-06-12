<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'payment_method',
        'payment_status',
        'payment_token',
        'payment_url',
        'shipping_address',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'shipping_cost',
        'total_price'  // Add this line
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'total_price' => 'decimal:2',  // Add this line
        'payment_status' => 'string'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with OrderItems
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tracking()
    {
        return $this->hasOne(ShippingTracking::class);
    }
}
