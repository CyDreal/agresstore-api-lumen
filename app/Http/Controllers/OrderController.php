<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\ShippingTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items.product', 'user'])->get();
        return response()->json([
            'status' => 1,
            'orders' => $orders
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['items.product', 'user'])->find($id);

        if (!$order) {
            return response()->json([
                'status' => 0,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'order' => $order
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'payment_method' => 'required|in:cod,midtrans',
            'shipping_address' => 'required',
            'shipping_city' => 'required',
            'shipping_province' => 'required',
            'shipping_postal_code' => 'required',
            'shipping_cost' => 'required|numeric',
            'courier' => 'required',
            'service' => 'required'
        ]);

        // Get cart items
        $cartItems = Cart::with('product')
            ->where('user_id', $request->user_id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Cart is empty'
            ], 400);
        }

        // Create order
        $order = Order::create([
            'user_id' => $request->user_id,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'shipping_address' => $request->shipping_address,
            'shipping_city' => $request->shipping_city,
            'shipping_province' => $request->shipping_province,
            'shipping_postal_code' => $request->shipping_postal_code,
            'shipping_cost' => $request->shipping_cost,
            'total_price' => $request->total_price // Add this line
        ]);

        // Create shipping tracking
        ShippingTracking::create([
            'order_id' => $order->id,
            'courier' => $request->courier,
            'service' => $request->service,
            'etd_days' => $request->etd_days ?? 0,
            'status' => $request->status
        ]);

        // Create order items from cart
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'subtotal' => $item->product->price * $item->quantity
            ]);
        }

        // Clear cart
        Cart::where('user_id', $request->user_id)->delete();

        // Load relationships
        $order->load(['items.product', 'tracking']);

        return response()->json([
            'status' => 1,
            'message' => 'Order created successfully',
            'order' => $order
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'shipping_start_date' => 'required_if:status,shipped|date',
            'estimated_arrival' => 'required_if:status,shipped|date',
            'actual_arrival' => 'required_if:status,delivered|date'
        ]);

        $order = Order::with('tracking')->find($id);

        if (!$order) {
            return response()->json([
                'status' => 0,
                'message' => 'Order not found'
            ], 404);
        }

        // Update shipping tracking status
        $order->tracking->update([
            'status' => $request->status,
            'shipping_start_date' => $request->shipping_start_date,
            'estimated_arrival' => $request->estimated_arrival,
            'actual_arrival' => $request->actual_arrival
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Order status updated successfully',
            'order' => $order->fresh(['tracking'])
        ]);
    }

    public function getUserOrders($userId)
    {
        $orders = Order::with(['items.product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 1,
            'orders' => $orders
        ]);
    }
}
