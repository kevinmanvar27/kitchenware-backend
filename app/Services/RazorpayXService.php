<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\VendorBankAccount;
use App\Models\VendorPayout;
use App\Models\PayoutLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RazorpayXService
{
    protected string $keyId;
    protected string $keySecret;
    protected string $accountNumber;
    protected string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        // Get settings from database, fallback to config/env
        $settings = Setting::first();
        
        // Ensure we always have string values, even if settings or config are null
        $this->keyId = $settings->razorpayx_key_id ?? config('services.razorpayx.key_id') ?? '';
        $this->keySecret = $settings->razorpayx_key_secret ?? config('services.razorpayx.key_secret') ?? '';
        $this->accountNumber = $settings->razorpayx_account_number ?? config('services.razorpayx.account_number') ?? '';
    }

    /**
     * Make authenticated API request to RazorpayX
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->timeout(30)
                ->$method($url, $data);

            $responseData = $response->json();

            if ($response->failed()) {
                Log::error('RazorpayX API Error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $responseData,
                ]);

                return [
                    'success' => false,
                    'error' => $responseData['error']['description'] ?? 'Unknown error occurred',
                    'error_code' => $responseData['error']['code'] ?? 'UNKNOWN',
                    'response' => $responseData,
                ];
            }

            return [
                'success' => true,
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            Log::error('RazorpayX API Exception', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'EXCEPTION',
            ];
        }
    }

    /**
     * Create a contact in RazorpayX
     * 
     * @param Vendor $vendor
     * @return array
     */
    public function createContact(Vendor $vendor): array
    {
        // Get a valid name for RazorpayX (alphabets and spaces only)
        $name = $this->sanitizeNameForRazorpay($vendor->user->name ?? $vendor->store_name);
        
        // Get valid phone number (10 digits)
        $phone = $this->sanitizePhoneNumber($vendor->business_phone ?? $vendor->user->mobile_number);
        
        $data = [
            'name' => $name,
            'email' => $vendor->business_email ?? $vendor->user->email,
            'contact' => $phone,
            'type' => 'vendor',
            'reference_id' => 'vendor_' . $vendor->id,
            'notes' => [
                'vendor_id' => (string) $vendor->id,
                'store_name' => $vendor->store_name,
            ],
        ];

        $result = $this->makeRequest('post', '/contacts', $data);

        if ($result['success']) {
            Log::info('RazorpayX Contact Created', [
                'vendor_id' => $vendor->id,
                'contact_id' => $result['data']['id'],
            ]);
        }

        return $result;
    }

    /**
     * Sanitize name for RazorpayX (only alphabets and spaces allowed)
     * 
     * @param string|null $name
     * @return string
     */
    protected function sanitizeNameForRazorpay(?string $name): string
    {
        if (empty($name)) {
            return 'Vendor';
        }
        
        // If name looks like an email, extract the part before @
        if (filter_var($name, FILTER_VALIDATE_EMAIL)) {
            $name = explode('@', $name)[0];
        }
        
        // Remove all non-alphabetic characters except spaces
        $name = preg_replace('/[^a-zA-Z\s]/', ' ', $name);
        
        // Replace multiple spaces with single space
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Trim and ensure minimum length
        $name = trim($name);
        
        // If name is too short or empty after sanitization, use a default
        if (strlen($name) < 3) {
            return 'Vendor Account';
        }
        
        // Capitalize first letter of each word
        return ucwords(strtolower($name));
    }

    /**
     * Sanitize phone number for RazorpayX (10 digits only)
     * 
     * @param string|null $phone
     * @return string|null
     */
    protected function sanitizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }
        
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 91 and has 12 digits, remove country code
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }
        
        // Return only if it's a valid 10-digit number
        return strlen($phone) === 10 ? $phone : null;
    }

    /**
     * Create a fund account (bank account) in RazorpayX
     * 
     * @param VendorBankAccount $bankAccount
     * @param string $contactId
     * @return array
     */
    public function createFundAccount(VendorBankAccount $bankAccount, string $contactId): array
    {
        $data = [
            'contact_id' => $contactId,
            'account_type' => 'bank_account',
            'bank_account' => [
                'name' => $bankAccount->account_holder_name,
                'ifsc' => $bankAccount->ifsc_code,
                'account_number' => $bankAccount->account_number,
            ],
        ];

        $result = $this->makeRequest('post', '/fund_accounts', $data);

        if ($result['success']) {
            Log::info('RazorpayX Fund Account Created', [
                'vendor_id' => $bankAccount->vendor_id,
                'fund_account_id' => $result['data']['id'],
            ]);
        }

        return $result;
    }

    /**
     * Create a payout in RazorpayX
     * 
     * @param VendorPayout $payout
     * @param string $fundAccountId
     * @param string $mode NEFT, RTGS, IMPS, UPI
     * @return array
     */
    public function createPayout(VendorPayout $payout, string $fundAccountId, string $mode = 'NEFT'): array
    {
        // Narration must be max 30 characters for RazorpayX
        $narration = 'Payout VND' . $payout->vendor_id;
        
        $data = [
            'account_number' => $this->accountNumber,
            'fund_account_id' => $fundAccountId,
            'amount' => (int) round($payout->amount * 100), // Convert to paise
            'currency' => 'INR',
            'mode' => $mode,
            'purpose' => 'payout',
            'queue_if_low_balance' => true,
            'reference_id' => 'payout_' . $payout->id,
            'narration' => $narration,
            'notes' => [
                'payout_id' => (string) $payout->id,
                'vendor_id' => (string) $payout->vendor_id,
                'store_name' => $payout->vendor->store_name,
            ],
        ];

        $result = $this->makeRequest('post', '/payouts', $data);

        if ($result['success']) {
            Log::info('RazorpayX Payout Created', [
                'payout_id' => $payout->id,
                'razorpay_payout_id' => $result['data']['id'],
                'status' => $result['data']['status'],
            ]);
        }

        return $result;
    }

    /**
     * Get payout status from RazorpayX
     * 
     * @param string $payoutId
     * @return array
     */
    public function getPayoutStatus(string $payoutId): array
    {
        return $this->makeRequest('get', '/payouts/' . $payoutId);
    }

    /**
     * Get account balance from RazorpayX
     * 
     * @return array
     */
    public function getBalance(): array
    {
        $result = $this->makeRequest('get', '/balance?account_number=' . $this->accountNumber);

        if ($result['success']) {
            // Convert paise to rupees
            $result['data']['balance_rupees'] = ($result['data']['balance'] ?? 0) / 100;
        }

        return $result;
    }

    /**
     * Get contact details from RazorpayX
     * 
     * @param string $contactId
     * @return array
     */
    public function getContact(string $contactId): array
    {
        return $this->makeRequest('get', '/contacts/' . $contactId);
    }

    /**
     * Get fund account details from RazorpayX
     * 
     * @param string $fundAccountId
     * @return array
     */
    public function getFundAccount(string $fundAccountId): array
    {
        return $this->makeRequest('get', '/fund_accounts/' . $fundAccountId);
    }

    /**
     * Setup vendor for payouts (create contact and fund account)
     * 
     * @param Vendor $vendor
     * @param VendorBankAccount $bankAccount
     * @return array
     */
    public function setupVendorForPayouts(Vendor $vendor, VendorBankAccount $bankAccount): array
    {
        // Step 1: Create contact if not exists
        $contactId = $bankAccount->razorpay_contact_id;
        
        if (!$contactId) {
            $contactResult = $this->createContact($vendor);
            
            if (!$contactResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Failed to create contact: ' . $contactResult['error'],
                    'step' => 'contact_creation',
                ];
            }
            
            $contactId = $contactResult['data']['id'];
            $bankAccount->update(['razorpay_contact_id' => $contactId]);
        }

        // Step 2: Create fund account if not exists
        if (!$bankAccount->razorpay_fund_account_id) {
            $fundAccountResult = $this->createFundAccount($bankAccount, $contactId);
            
            if (!$fundAccountResult['success']) {
                $bankAccount->update([
                    'fund_account_status' => VendorBankAccount::FUND_STATUS_FAILED,
                    'fund_account_error' => $fundAccountResult['error'],
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Failed to create fund account: ' . $fundAccountResult['error'],
                    'step' => 'fund_account_creation',
                ];
            }
            
            $bankAccount->update([
                'razorpay_fund_account_id' => $fundAccountResult['data']['id'],
                'fund_account_status' => VendorBankAccount::FUND_STATUS_CREATED,
                'fund_account_error' => null,
            ]);
        }

        return [
            'success' => true,
            'contact_id' => $contactId,
            'fund_account_id' => $bankAccount->razorpay_fund_account_id,
        ];
    }

    /**
     * Process a vendor payout
     * 
     * @param VendorPayout $payout
     * @param string $mode
     * @return array
     */
    public function processPayout(VendorPayout $payout, string $mode = 'NEFT'): array
    {
        $vendor = $payout->vendor;
        $bankAccount = $vendor->primaryBankAccount;

        if (!$bankAccount) {
            return [
                'success' => false,
                'error' => 'No bank account found for vendor',
            ];
        }

        // Ensure vendor is setup for payouts
        if (!$bankAccount->hasFundAccount()) {
            $setupResult = $this->setupVendorForPayouts($vendor, $bankAccount);
            
            if (!$setupResult['success']) {
                $payout->update([
                    'status' => VendorPayout::STATUS_FAILED,
                    'failure_reason' => $setupResult['error'],
                ]);
                $payout->addLog(PayoutLog::EVENT_FAILED, null, null, $setupResult['error']);
                
                return $setupResult;
            }
            
            // Refresh bank account
            $bankAccount->refresh();
        }

        // Create payout in RazorpayX
        $payout->update([
            'status' => VendorPayout::STATUS_PROCESSING,
            'razorpay_fund_account_id' => $bankAccount->razorpay_fund_account_id,
            'payout_mode' => $mode,
        ]);
        $payout->addLog(PayoutLog::EVENT_INITIATED, null, null, 'Payout initiated');

        $result = $this->createPayout($payout, $bankAccount->razorpay_fund_account_id, $mode);

        if (!$result['success']) {
            $payout->update([
                'status' => VendorPayout::STATUS_FAILED,
                'failure_reason' => $result['error'],
                'retry_count' => $payout->retry_count + 1,
            ]);
            $payout->addLog(PayoutLog::EVENT_FAILED, null, $result['response'] ?? null, $result['error']);
            
            return $result;
        }

        // Update payout with RazorpayX details
        $razorpayData = $result['data'];
        $payout->update([
            'razorpay_payout_id' => $razorpayData['id'],
            'razorpay_status' => $razorpayData['status'],
            'utr' => $razorpayData['utr'] ?? null,
            'status' => $this->mapRazorpayStatus($razorpayData['status']),
        ]);
        $payout->addLog(PayoutLog::EVENT_PROCESSING, $razorpayData['status'], $razorpayData, 'Payout submitted to RazorpayX');

        return [
            'success' => true,
            'data' => $razorpayData,
        ];
    }

    /**
     * Map RazorpayX status to internal status
     */
    protected function mapRazorpayStatus(string $razorpayStatus): string
    {
        return match($razorpayStatus) {
            'queued', 'pending' => VendorPayout::STATUS_PENDING,
            'processing' => VendorPayout::STATUS_PROCESSING,
            'processed' => VendorPayout::STATUS_COMPLETED,
            'rejected', 'cancelled' => VendorPayout::STATUS_REJECTED,
            'failed' => VendorPayout::STATUS_FAILED,
            'reversed' => VendorPayout::STATUS_REVERSED,
            default => VendorPayout::STATUS_PROCESSING,
        };
    }

    /**
     * Verify webhook signature
     * 
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.razorpayx.webhook_secret');
        
        if (!$webhookSecret) {
            Log::warning('RazorpayX webhook secret not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle webhook event
     * 
     * @param array $payload
     * @return bool
     */
    public function handleWebhook(array $payload): bool
    {
        $event = $payload['event'] ?? null;
        $payloadData = $payload['payload'] ?? [];

        Log::info('RazorpayX Webhook Received', ['event' => $event]);

        switch ($event) {
            case 'payout.processed':
            case 'payout.reversed':
            case 'payout.failed':
            case 'payout.rejected':
                return $this->handlePayoutWebhook($payloadData, $event);
            default:
                Log::info('Unhandled RazorpayX webhook event', ['event' => $event]);
                return true;
        }
    }

    /**
     * Handle payout webhook events
     */
    protected function handlePayoutWebhook(array $payloadData, string $event): bool
    {
        $payoutData = $payloadData['payout']['entity'] ?? [];
        $razorpayPayoutId = $payoutData['id'] ?? null;

        if (!$razorpayPayoutId) {
            Log::error('RazorpayX webhook missing payout ID');
            return false;
        }

        $payout = VendorPayout::where('razorpay_payout_id', $razorpayPayoutId)->first();

        if (!$payout) {
            Log::warning('RazorpayX webhook: Payout not found', ['razorpay_payout_id' => $razorpayPayoutId]);
            return false;
        }

        $newStatus = $this->mapRazorpayStatus($payoutData['status']);
        
        $payout->update([
            'status' => $newStatus,
            'razorpay_status' => $payoutData['status'],
            'utr' => $payoutData['utr'] ?? $payout->utr,
            'processed_at' => in_array($newStatus, [VendorPayout::STATUS_COMPLETED, VendorPayout::STATUS_FAILED]) 
                ? now() 
                : $payout->processed_at,
            'failure_reason' => $payoutData['failure_reason'] ?? $payout->failure_reason,
        ]);

        $eventType = match($event) {
            'payout.processed' => PayoutLog::EVENT_COMPLETED,
            'payout.reversed' => PayoutLog::EVENT_REVERSED,
            'payout.failed', 'payout.rejected' => PayoutLog::EVENT_FAILED,
            default => PayoutLog::EVENT_PROCESSING,
        };

        $payout->addLog($eventType, $payoutData['status'], $payoutData, "Webhook: {$event}");

        // Note: Wallet is deducted when payout status becomes 'processing' in ProcessBulkPayouts job
        // No need to deduct again here to avoid double-deduction
        
        // Handle failed/reversed payouts - refund to wallet
        if (in_array($newStatus, [VendorPayout::STATUS_FAILED, VendorPayout::STATUS_REVERSED])) {
            $wallet = $payout->vendor->wallet;
            if ($wallet) {
                // Refund the amount back to pending since payout failed
                $wallet->increment('pending_amount', $payout->amount);
                $wallet->decrement('total_paid', $payout->amount);
                Log::info('Payout failed/reversed - refunded to wallet', [
                    'payout_id' => $payout->id,
                    'amount' => $payout->amount
                ]);
            }
        }

        Log::info('RazorpayX webhook processed', [
            'payout_id' => $payout->id,
            'event' => $event,
            'new_status' => $newStatus,
        ]);

        return true;
    }
}
