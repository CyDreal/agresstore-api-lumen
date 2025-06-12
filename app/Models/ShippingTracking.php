<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingTracking extends Model
{
    protected $table = 'shipping_tracking';

    protected $fillable = [
        'order_id',
        'courier',
        'service',
        'etd_days',
        'status',
        'shipping_start_date',
        'estimated_arrival',
        'actual_arrival'
    ];

    protected $casts = [
        'shipping_start_date' => 'datetime',
        'estimated_arrival' => 'datetime',
        'actual_arrival' => 'datetime',
        'etd_days' => 'integer'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
