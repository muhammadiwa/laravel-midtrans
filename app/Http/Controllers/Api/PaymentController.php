<?php

namespace App\Http\Controllers\Api;

use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Sawirricardo\Midtrans\Laravel\Facades\Midtrans; // Import Midtrans facade

class PaymentController extends Controller
{
    public function createTransaction(Request $request)
    {
        // Validasi input dari request
        $validated = $request->validate([
            'order_id' => 'required|string',
            'gross_amount' => 'required|numeric',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
        ]);

        // Set parameter transaksi
        $params = [
            'transaction_details' => [
                'order_id' => $validated['order_id'],
                'gross_amount' => $validated['gross_amount'],
            ],
            'customer_details' => [
                'first_name' => $validated['customer_name'],
                'email' => $validated['customer_email'],
                'phone' => $validated['customer_phone'],
            ]
        ];

        try {
            // Buat Snap Transaction Token
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'status' => 200,
                'message' => 'Transaction created successfully',
                'snap_token' => $snapToken,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function transactionStatus(Request $request, $orderId)
    {
        try {
            // Mendapatkan status transaksi
            $status = Transaction::status($orderId);

            return response()->json([
                'status' => 200,
                'message' => 'Transaction status retrieved successfully',
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
