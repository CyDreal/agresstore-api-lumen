<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // Get carts with product relationships
        $carts = Cart::with('product.images')
            ->where('user_id', $request->user_id)
            ->get();

        // Transform collection to add subtotal
        $cartData = [];
        $total = 0;

        foreach ($carts as $cart) {
            $subtotal = $cart->quantity * $cart->product->price;
            $total += $subtotal;

            $cartData[] = [
                'id' => $cart->id,
                'user_id' => $cart->user_id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity,
                'subtotal' => $subtotal,
                'product' => $cart->product,
                'created_at' => $cart->created_at,
                'updated_at' => $cart->updated_at
            ];
        }

        return response()->json([
            'status' => 1,
            'carts' => $cartData,
            'total' => $total
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json([
                'status' => 0,
                'message' => 'Stock not available'
            ], 400);
        }

        $cart = Cart::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'product_id' => $request->product_id
            ],
            [
                'quantity' => $request->quantity
            ]
        );

        return response()->json([
            'status' => 1,
            'message' => 'Product added to cart',
            'cart' => $cart
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json([
                'status' => 0,
                'message' => 'Cart not found'
            ], 404);
        }

        if ($cart->product->stock < $request->quantity) {
            return response()->json([
                'status' => 0,
                'message' => 'Stock not available'
            ], 400);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json([
            'status' => 1,
            'message' => 'Cart updated',
            'cart' => $cart
        ]);
    }

    public function destroy($id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json([
                'status' => 0,
                'message' => 'Cart not found'
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Cart item removed'
        ]);
    }
}
