<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $table = 'shipping_address';

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'province_id',
        'province_name',
        'city_id',
        'city_name',
        'full_address',
        'postal_code',
        'notes',      // Add this line
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'province_id' => 'integer',
        'city_id' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
