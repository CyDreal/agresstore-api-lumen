<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = env('MIDTRANS_SANITIZED', true);
        Config::$is3ds = env('MIDTRANS_3DS', true);
    }

    public function createTransaction($order)
    {
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'id' => $item->product_id,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'name' => $item->product->product_name
            ];
        }

        // Add shipping cost as an item
        $items[] = [
            'id' => 'shipping',
            'price' => $order->shipping_cost,
            'quantity' => 1,
            'name' => 'Shipping Cost'
        ];

        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . $order->id,
                'gross_amount' => $order->total_price + $order->shipping_cost,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->user->username,
                'email' => $order->user->email,
                'phone' => $order->user->phone ?? '',
                'shipping_address' => [
                    'address' => $order->shipping_address,
                    'city' => $order->shipping_city,
                    'postal_code' => $order->shipping_postal_code
                ]
            ]
        ];

        return Snap::createTransaction($params);
    }
}
