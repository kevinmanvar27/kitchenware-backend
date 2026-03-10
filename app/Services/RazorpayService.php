<?php

namespace App\Services;

use Razorpay\Api\Api;
use App\Models\ProformaInvoice;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Exception;

class RazorpayService
{
    protected $api;
    protected $keyId;
    protected $keySecret;
    protected $razorpayXAccountId;

    public function __construct()
    {
        // Get settings from database
        $settings = Setting::first();
        
        // Use Razorpay credentials from settings (for customer payments)
        $this->keyId = $settings->razorpay_key_id ?? config('services.razorpay.key_id');
        $this->keySecret = $settings->razorpay_key_secret ?? config('services.razorpay.key_secret');
        
        // Use RazorpayX account number for Route transfers
        $this->razorpayXAccountId = $settings->razorpayx_account_number ?? config('services.razorpayx.account_number');
        
        // Initialize Razorpay API only if credentials are available
        if ($this->keyId && $this->keySecret) {
            $this->api = new Api($this->keyId, $this->keySecret);
        }
    }

    /**
     * Create Razorpay order with Route (automatic split to RazorpayX)
     * 
     * @param ProformaInvoice $invoice
     * @param float $amount Amount in INR
     * @return array
     */
    public function createOrderWithRoute(ProformaInvoice $invoice, float $amount): array
    {
        try {
            // Check if API is initialized
            if (!$this->api) {
                return [
                    'success' => false,
                    'error' => 'Razorpay credentials not configured',
                ];
            }

            $amountInPaise = (int) round($amount * 100);
            
            $orderData = [
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => 'invoice_' . $invoice->invoice_number,
                'notes' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'vendor_id' => $invoice->vendor_id,
                    'customer_id' => $invoice->vendor_customer_id,
                ],
            ];

            // Add Route transfer to RazorpayX (automatic split)
            // This ensures money automatically goes to your RazorpayX account
            if ($this->razorpayXAccountId) {
                $orderData['transfers'] = [
                    [
                        'account' => $this->razorpayXAccountId, // Your RazorpayX account
                        'amount' => $amountInPaise,
                        'currency' => 'INR',
                        'on_hold' => 0, // 0 = immediate transfer, 1 = hold
                        'notes' => [
                            'invoice_id' => $invoice->id,
                            'vendor_id' => $invoice->vendor_id,
                        ],
                    ],
                ];
            }

            $order = $this->api->order->create($orderData);

            Log::info('Razorpay Order Created with Route', [
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'route_enabled' => !empty($this->razorpayXAccountId),
            ]);

            return [
                'success' => true,
                'order_id' => $order->id,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'key_id' => $this->keyId,
            ];

        } catch (Exception $e) {
            Log::error('Razorpay Order Creation Failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment signature
     * 
     * @param string $orderId
     * @param string $paymentId
     * @param string $signature
     * @return bool
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        try {
            if (!$this->api) {
                Log::error('Razorpay API not initialized for verification');
                return false;
            }

            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ];

            $this->api->utility->verifyPaymentSignature($attributes);
            
            Log::info('Razorpay Payment Signature Verified', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
            ]);
            
            return true;

        } catch (Exception $e) {
            Log::error('Razorpay Payment Verification Failed', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get payment details
     * 
     * @param string $paymentId
     * @return array|null
     */
    public function getPaymentDetails(string $paymentId): ?array
    {
        try {
            if (!$this->api) {
                Log::error('Razorpay API not initialized for fetching payment details');
                return null;
            }

            $payment = $this->api->payment->fetch($paymentId);
            
            Log::info('Razorpay Payment Details Fetched', [
                'payment_id' => $paymentId,
                'status' => $payment->status ?? 'unknown',
            ]);
            
            return $payment->toArray();
            
        } catch (Exception $e) {
            Log::error('Failed to fetch payment details', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if Razorpay is configured
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->keyId) && !empty($this->keySecret);
    }

    /**
     * Check if Route is enabled (RazorpayX account configured)
     * 
     * @return bool
     */
    public function isRouteEnabled(): bool
    {
        return !empty($this->razorpayXAccountId);
    }
}
