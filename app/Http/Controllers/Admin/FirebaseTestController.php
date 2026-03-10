<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FirebaseTestController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Run a comprehensive test of Firebase configuration
     *
     * @return \Illuminate\Http\Response
     */
    public function runDiagnostics()
    {
        // Step 1: Check if Firebase is configured
        $configTest = $this->notificationService->testConfiguration();
        
        $results = [
            'config_test' => $configTest,
            'firebase_settings' => [
                'project_id' => firebase_project_id(),
                'client_email' => firebase_client_email(),
                'private_key_set' => !empty(firebase_private_key()),
            ],
            'token_generation' => null,
            'recommendations' => []
        ];
        
        // Step 2: Try to generate an access token
        try {
            // This is a protected method, so we'll need to call it via reflection
            $reflectionClass = new \ReflectionClass(NotificationService::class);
            $method = $reflectionClass->getMethod('generateAccessToken');
            $method->setAccessible(true);
            
            $token = $method->invoke($this->notificationService);
            
            $results['token_generation'] = [
                'success' => !empty($token),
                'token' => $token ? substr($token, 0, 10) . '...' : null
            ];
            
            if (empty($token)) {
                $results['recommendations'][] = 'Failed to generate Firebase access token. Please check your private key format.';
            }
        } catch (\Exception $e) {
            Log::error('Firebase token generation diagnostic error', [
                'error' => $e->getMessage()
            ]);
            
            $results['token_generation'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            $results['recommendations'][] = 'Error during token generation: ' . $e->getMessage();
        }
        
        // Step 3: Check for common issues and provide recommendations
        if (empty(firebase_project_id())) {
            $results['recommendations'][] = 'Firebase Project ID is missing. Please enter your Firebase project ID from the Firebase Console.';
        }
        
        if (empty(firebase_client_email())) {
            $results['recommendations'][] = 'Firebase Client Email is missing. Please enter the service account email from your Firebase service account JSON file.';
        }
        
        if (empty(firebase_private_key())) {
            $results['recommendations'][] = 'Firebase Private Key is missing. Please enter the private key from your Firebase service account JSON file.';
        } else {
            $privateKey = firebase_private_key();
            
            // Check if the private key has proper formatting
            if (!str_contains($privateKey, '-----BEGIN PRIVATE KEY-----') || !str_contains($privateKey, '-----END PRIVATE KEY-----')) {
                $results['recommendations'][] = 'Firebase Private Key appears to be malformed. It should include the BEGIN and END markers.';
            }
            
            // Check if the private key has proper newlines
            if (!str_contains($privateKey, "\n")) {
                $results['recommendations'][] = 'Firebase Private Key may be missing proper line breaks. Make sure the key includes proper newlines.';
            }
        }
        
        return view('admin.firebase.diagnostics', compact('results'));
    }
    
    /**
     * Fix common Firebase configuration issues
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fixConfiguration(Request $request)
    {
        $request->validate([
            'fix_type' => 'required|string|in:private_key_format',
            'private_key' => 'required_if:fix_type,private_key_format|string'
        ]);
        
        $success = false;
        $message = 'No changes were made.';
        
        if ($request->fix_type === 'private_key_format') {
            try {
                // Get the settings model
                $settings = \App\Models\Setting::first();
                
                if ($settings) {
                    // Fix the private key format
                    $privateKey = $request->private_key;
                    
                    // Ensure the key has proper BEGIN/END markers
                    if (!str_contains($privateKey, '-----BEGIN PRIVATE KEY-----')) {
                        $privateKey = "-----BEGIN PRIVATE KEY-----\n" . $privateKey;
                    }
                    
                    if (!str_contains($privateKey, '-----END PRIVATE KEY-----')) {
                        $privateKey .= "\n-----END PRIVATE KEY-----";
                    }
                    
                    // Replace any escaped newlines with actual newlines
                    $privateKey = str_replace('\\n', "\n", $privateKey);
                    
                    // Save the fixed key
                    $settings->firebase_private_key = $privateKey;
                    $settings->save();
                    
                    $success = true;
                    $message = 'Firebase private key format has been fixed.';
                } else {
                    $message = 'Settings record not found.';
                }
            } catch (\Exception $e) {
                Log::error('Firebase fix configuration error', [
                    'error' => $e->getMessage()
                ]);
                $message = 'Error fixing configuration: ' . $e->getMessage();
            }
        }
        
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }

    /**
     * Show the Firebase setup guide
     *
     * @return \Illuminate\Http\Response
     */
    public function showSetupGuide()
    {
        return view('admin.firebase.setup-guide');
    }
}