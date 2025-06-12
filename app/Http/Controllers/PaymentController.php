<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function getPaymentToken($orderId)
    {
        $order = Order::with(['items.product', 'user'])->find($orderId);

        if (!$order) {
            return response()->json([
                'status' => 0,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            $snap = $this->midtransService->createTransaction($order);

            $order->update([
                'payment_token' => $snap->token,
                'payment_url' => $snap->redirect_url
            ]);

            return response()->json([
                'status' => 1,
                'message' => 'Payment token generated',
                'data' => [
                    'token' => $snap->token,
                    'payment_url' => $snap->redirect_url,
                    'order_id' => $order->id
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Token Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Failed to generate payment token'
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        try {
            $notification = new Notification();

            Log::info('Midtrans Webhook', [
                'order_id' => $notification->order_id,
                'status_code' => $notification->status_code,
                'transaction_status' => $notification->transaction_status
            ]);

            $orderNumber = str_replace('ORDER-', '', $notification->order_id);
            $order = Order::find($orderNumber);

            if (!$order) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Order not found'
                ], 404);
            }

            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;

            $paymentStatus = $this->getPaymentStatus($transactionStatus, $fraudStatus);

            $order->update(['payment_status' => $paymentStatus]);

            // If payment is successful
            if ($paymentStatus === 'paid') {
                // Update shipping tracking status
                $order->tracking()->update([
                    'status' => 'processing'
                ]);

                // Update product stock
                foreach ($order->items as $item) {
                    $item->product->decrement('stock', $item->quantity);
                    $item->product->increment('purchased_quantity', $item->quantity);
                }
            }

            return response()->json([
                'status' => 1,
                'message' => 'Webhook handled successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Failed to handle webhook'
            ], 500);
        }
    }

    private function getPaymentStatus($transactionStatus, $fraudStatus)
    {
        if ($transactionStatus == 'capture') {
            return ($fraudStatus == 'accept') ? 'paid' : 'pending';
        } else if ($transactionStatus == 'settlement') {
            return 'paid';
        } else if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            return 'expired';
        } else if ($transactionStatus == 'pending') {
            return 'unpaid';
        }

        return 'failed';
    }
}
