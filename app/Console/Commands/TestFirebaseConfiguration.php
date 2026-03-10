<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class TestFirebaseConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Firebase Cloud Messaging configuration';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('Testing Firebase Cloud Messaging configuration...');
        
        // Step 1: Check if Firebase is configured
        $this->info('Checking Firebase configuration...');
        $configTest = $notificationService->testConfiguration();
        
        if ($configTest['success']) {
            $this->info('✅ Firebase is properly configured.');
        } else {
            $this->error('❌ Firebase configuration issue: ' . $configTest['message']);
            $this->table(
                ['Setting', 'Status'],
                [
                    ['Project ID', !empty(firebase_project_id()) ? '✅ Set' : '❌ Missing'],
                    ['Client Email', !empty(firebase_client_email()) ? '✅ Set' : '❌ Missing'],
                    ['Private Key', !empty(firebase_private_key()) ? '✅ Set' : '❌ Missing'],
                ]
            );
        }
        
        // Step 2: Try to generate an access token
        $this->info('Attempting to generate Firebase access token...');
        try {
            // This is a protected method, so we'll need to call it via reflection
            $reflectionClass = new \ReflectionClass(NotificationService::class);
            $method = $reflectionClass->getMethod('generateAccessToken');
            $method->setAccessible(true);
            
            $token = $method->invoke($notificationService);
            
            if (!empty($token)) {
                $this->info('✅ Successfully generated Firebase access token: ' . substr($token, 0, 20) . '...');
            } else {
                $this->error('❌ Failed to generate Firebase access token.');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error during token generation: ' . $e->getMessage());
            Log::error('Firebase token generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // Step 3: Check for common issues and provide recommendations
        $this->info('Checking for common issues...');
        $recommendations = [];
        
        if (empty(firebase_project_id())) {
            $recommendations[] = 'Firebase Project ID is missing. Please enter your Firebase project ID from the Firebase Console.';
        }
        
        if (empty(firebase_client_email())) {
            $recommendations[] = 'Firebase Client Email is missing. Please enter the service account email from your Firebase service account JSON file.';
        }
        
        if (empty(firebase_private_key())) {
            $recommendations[] = 'Firebase Private Key is missing. Please enter the private key from your Firebase service account JSON file.';
        } else {
            $privateKey = firebase_private_key();
            
            // Check if the private key has proper formatting
            if (!str_contains($privateKey, '-----BEGIN PRIVATE KEY-----') || !str_contains($privateKey, '-----END PRIVATE KEY-----')) {
                $recommendations[] = 'Firebase Private Key appears to be malformed. It should include the BEGIN and END markers.';
            }
            
            // Check if the private key has proper newlines
            if (!str_contains($privateKey, "\n")) {
                $recommendations[] = 'Firebase Private Key may be missing proper line breaks. Make sure the key includes proper newlines.';
            }
        }
        
        if (!empty($recommendations)) {
            $this->warn('Recommendations:');
            foreach ($recommendations as $recommendation) {
                $this->line(' - ' . $recommendation);
            }
            
            if (empty(firebase_project_id()) || empty(firebase_client_email()) || empty(firebase_private_key())) {
                $this->newLine();
                $this->info('💡 TIP: You can view a complete Firebase setup guide in the admin panel:');
                $this->line('    http://127.0.0.1:8000/admin/firebase/setup-guide');
            }
        } else {
            $this->info('✅ No common issues detected.');
        }
        
        return Command::SUCCESS;
    }
}