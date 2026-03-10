@extends('admin.layouts.app')

@section('title', 'Firebase Setup Guide')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Firebase Cloud Messaging Setup Guide</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h5 class="card-title mb-0">Step-by-Step Firebase Setup Guide</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Follow this guide to set up Firebase Cloud Messaging for push notifications in your application.
                            </div>
                            
                            <div class="mb-4">
                                <h5>Step 1: Create a Firebase Project</h5>
                                <ol class="list-group list-group-numbered mb-3">
                                    <li class="list-group-item">Go to the <a href="https://console.firebase.google.com/" target="_blank">Firebase Console</a></li>
                                    <li class="list-group-item">Click on "Add project" and follow the setup wizard</li>
                                    <li class="list-group-item">Enter a project name and accept the terms</li>
                                    <li class="list-group-item">Enable or disable Google Analytics as per your preference</li>
                                    <li class="list-group-item">Click "Create project"</li>
                                </ol>
                                <div class="text-center mb-3">
                                    <img src="https://firebase.google.com/docs/projects/images/firebase-projects-0.png" alt="Create Firebase Project" class="img-fluid border rounded" style="max-width: 600px;">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h5>Step 2: Generate a Service Account Key</h5>
                                <ol class="list-group list-group-numbered mb-3">
                                    <li class="list-group-item">In your Firebase project, go to Project Settings (gear icon in the top left)</li>
                                    <li class="list-group-item">Navigate to the "Service accounts" tab</li>
                                    <li class="list-group-item">Click on "Generate new private key" button</li>
                                    <li class="list-group-item">Save the JSON file securely - it contains sensitive information</li>
                                </ol>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Keep your service account key secure! Anyone with this file can access your Firebase project.
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h5>Step 3: Configure Your Application</h5>
                                <ol class="list-group list-group-numbered mb-3">
                                    <li class="list-group-item">Open the downloaded JSON file</li>
                                    <li class="list-group-item">Copy the <code>project_id</code> value to the "Firebase Project ID" field in your application settings</li>
                                    <li class="list-group-item">Copy the <code>client_email</code> value to the "Firebase Client Email" field</li>
                                    <li class="list-group-item">Copy the <code>private_key</code> value to the "Firebase Private Key" field (include the BEGIN and END markers)</li>
                                </ol>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    The private key should look like: <code>-----BEGIN PRIVATE KEY-----\nMIIEvQIB...\n-----END PRIVATE KEY-----\n</code>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h5>Step 4: Set Up Your Mobile App</h5>
                                <ol class="list-group list-group-numbered mb-3">
                                    <li class="list-group-item">In Firebase Console, add your iOS and/or Android app</li>
                                    <li class="list-group-item">Follow the setup instructions for each platform</li>
                                    <li class="list-group-item">Download the configuration files (google-services.json for Android, GoogleService-Info.plist for iOS)</li>
                                    <li class="list-group-item">Add these files to your mobile app projects</li>
                                    <li class="list-group-item">Implement FCM in your mobile apps to register device tokens</li>
                                </ol>
                            </div>
                            
                            <div class="mb-4">
                                <h5>Step 5: Test Your Configuration</h5>
                                <p>After setting up Firebase, you can test your configuration:</p>
                                <ol class="list-group list-group-numbered mb-3">
                                    <li class="list-group-item">Save your Firebase settings in the application</li>
                                    <li class="list-group-item">Run the Firebase diagnostics to check if everything is configured correctly</li>
                                    <li class="list-group-item">Send a test notification to verify it works</li>
                                </ol>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                    <a href="{{ route('admin.settings') }}" class="btn btn-primary">
                                        <i class="fas fa-cog"></i> Go to Settings
                                    </a>
                                    <a href="{{ route('admin.firebase.diagnostics') }}" class="btn btn-info">
                                        <i class="fas fa-stethoscope"></i> Run Diagnostics
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection