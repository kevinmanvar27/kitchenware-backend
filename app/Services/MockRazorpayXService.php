<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\VendorBankAccount;
use App\Models\VendorPayout;
use App\Models\PayoutLog;
use Illuminate\Support\Facades\Log;

class MockRazorpayXService extends RazorpayXService
{
    /**
     * Override makeRequest to return mock responses
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        Log::info('Mock RazorpayX API Request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'data' => $data
        ]);

        // Mock different responses based on the endpoint
        if (str_contains($endpoint, '/contacts')) {
            return [
                'success' => true,
                'data' => [
                    'id' => 'cont_' . uniqid(),
                    'name' => $data['name'] ?? 'Test Contact',
                    'email' => $data['email'] ?? 'test@example.com',
                    'contact' => $data['contact'] ?? '9876543210',
                    'type' => $data['type'] ?? 'vendor',
                    'active' => true,
                ]
            ];
        } elseif (str_contains($endpoint, '/fund_accounts')) {
            return [
                'success' => true,
                'data' => [
                    'id' => 'fa_' . uniqid(),
                    'contact_id' => $data['contact_id'] ?? 'cont_123456',
                    'account_type' => $data['account_type'] ?? 'bank_account',
                    'bank_account' => $data['bank_account'] ?? [
                        'name' => 'Test Account',
                        'ifsc' => 'HDFC0000001',
                        'account_number' => '1234567890'
                    ],
                    'active' => true,
                ]
            ];
        } elseif (str_contains($endpoint, '/payouts')) {
            if ($method === 'post') {
                return [
                    'success' => true,
                    'data' => [
                        'id' => 'pout_' . uniqid(),
                        'fund_account_id' => $data['fund_account_id'] ?? 'fa_123456',
                        'amount' => $data['amount'] ?? 100000,
                        'currency' => $data['currency'] ?? 'INR',
                        'mode' => $data['mode'] ?? 'NEFT',
                        'purpose' => $data['purpose'] ?? 'payout',
                        'status' => 'processing',
                        'utr' => 'UTR' . rand(1000000, 9999999),
                        'created_at' => now()->timestamp,
                    ]
                ];
            } else {
                // For GET requests (status check)
                return [
                    'success' => true,
                    'data' => [
                        'id' => str_replace('/payouts/', '', $endpoint),
                        'fund_account_id' => 'fa_' . uniqid(),
                        'amount' => 100000,
                        'currency' => 'INR',
                        'mode' => 'NEFT',
                        'purpose' => 'payout',
                        'status' => 'processed', // Always return processed for testing
                        'utr' => 'UTR' . rand(1000000, 9999999),
                        'created_at' => now()->timestamp,
                    ]
                ];
            }
        } elseif (str_contains($endpoint, '/balance')) {
            return [
                'success' => true,
                'data' => [
                    'balance' => 1000000, // 10,000 rupees in paise
                    'balance_rupees' => 10000.00,
                    'currency' => 'INR',
                ]
            ];
        }

        // Default response
        return [
            'success' => true,
            'data' => [
                'message' => 'Mock response for ' . $endpoint
            ]
        ];
    }
}