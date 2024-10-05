<?php

namespace App\Http\Controllers\Api;

use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Payment API Documentation",
 *      description="API Documentation for Payment Processing using Midtrans",
 *      @OA\Contact(
 *          email="support@example.com"
 *      ),
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8000",
 *      description="Local Development Server"
 * )
 *
 * @OA\Tag(
 *     name="Payments",
 *     description="API Endpoints for Payment Processing"
 * )
 */
class PaymentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/create-transaction",
     *     operationId="createTransaction",
     *     tags={"Payments"},
     *     summary="Create a new payment transaction",
     *     description="Creates a new payment transaction and returns a Snap token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id","gross_amount","customer_name","customer_email","customer_phone"},
     *             @OA\Property(property="order_id", type="string", example="ORDER-123456789"),
     *             @OA\Property(property="gross_amount", type="number", example=100000),
     *             @OA\Property(property="customer_name", type="string", example="John Doe"),
     *             @OA\Property(property="customer_email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="customer_phone", type="string", example="+6281234567890"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Transaction created successfully"),
     *             @OA\Property(property="snap_token", type="string", example="eJx9j...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/transaction-status/{orderId}",
     *     operationId="transactionStatus",
     *     tags={"Payments"},
     *     summary="Get the status of a payment transaction",
     *     description="Retrieves the status of a payment transaction by order ID.",
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         description="Order ID of the transaction",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="ORDER-123456789"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Transaction status retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="status_code", type="string", example="200"),
     *                 @OA\Property(property="transaction_status", type="string", example="settlement"),
     *                 @OA\Property(property="payment_type", type="string", example="bank_transfer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transaction not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
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
