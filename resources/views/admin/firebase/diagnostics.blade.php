@extends('admin.layouts.app')

@section('title', 'Firebase Diagnostics')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Firebase Diagnostics</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-primary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Settings
                    </a>
                    <a href="{{ route('admin.firebase.setup-guide') }}" class="btn btn-sm btn-info">
                        <i class="fas fa-book"></i> Setup Guide
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Firebase Configuration Status</h6>
                    <div>
                        <button class="btn btn-sm btn-primary" id="refreshDiagnostics">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Configuration Overview</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 200px;">Status</th>
                                        <td>
                                            @if($results['config_test']['success'])
                                                <span class="badge badge-success">Configured Correctly</span>
                                            @else
                                                <span class="badge badge-danger">Configuration Issues</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Project ID</th>
                                        <td>
                                            @if(!empty($results['firebase_settings']['project_id']))
                                                <span class="text-success">{{ $results['firebase_settings']['project_id'] }}</span>
                                            @else
                                                <span class="text-danger">Not set</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Client Email</th>
                                        <td>
                                            @if(!empty($results['firebase_settings']['client_email']))
                                                <span class="text-success">{{ $results['firebase_settings']['client_email'] }}</span>
                                            @else
                                                <span class="text-danger">Not set</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Private Key</th>
                                        <td>
                                            @if($results['firebase_settings']['private_key_set'])
                                                <span class="text-success">Set</span>
                                            @else
                                                <span class="text-danger">Not set</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Token Generation</th>
                                        <td>
                                            @if(isset($results['token_generation']['success']) && $results['token_generation']['success'])
                                                <span class="text-success">Success ({{ $results['token_generation']['token'] }})</span>
                                            @else
                                                <span class="text-danger">
                                                    Failed
                                                    @if(isset($results['token_generation']['error']))
                                                        - {{ $results['token_generation']['error'] }}
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(!empty($results['recommendations']))
                        <div class="mb-4">
                            <h5>Recommendations</h5>
                            <div class="alert alert-warning">
                                <ul class="mb-0">
                                    @foreach($results['recommendations'] as $recommendation)
                                        <li>{{ $recommendation }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h5>Common Fixes</h5>
                        <div class="accordion" id="fixesAccordion">
                            <div class="card">
                                <div class="card-header" id="headingPrivateKey">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapsePrivateKey" aria-expanded="true" aria-controls="collapsePrivateKey">
                                            Fix Private Key Format
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapsePrivateKey" class="collapse" aria-labelledby="headingPrivateKey" data-parent="#fixesAccordion">
                                    <div class="card-body">
                                        <p>If your private key is not working, it might be due to formatting issues. Use this tool to fix common private key format problems:</p>
                                        
                                        <form id="fixPrivateKeyForm">
                                            <div class="form-group">
                                                <label for="privateKey">Paste your private key from the Firebase service account JSON file:</label>
                                                <textarea class="form-control" id="privateKey" rows="10" placeholder="-----BEGIN PRIVATE KEY-----&#10;YOUR_PRIVATE_KEY_HERE&#10;-----END PRIVATE KEY-----"></textarea>
                                                <small class="form-text text-muted">This will properly format your key with correct line breaks and markers.</small>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Fix and Save</button>
                                        </form>
                                        <div id="fixKeyResult" class="mt-3" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Test Notification</h5>
                        <p>Send a test notification to verify your Firebase configuration:</p>
                        
                        <form id="testNotificationForm" class="form-inline">
                            <div class="form-group mr-2">
                                <input type="text" class="form-control" id="testDeviceToken" placeholder="Device token">
                            </div>
                            <button type="submit" class="btn btn-primary">Send Test Notification</button>
                        </form>
                        <div id="testNotificationResult" class="mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Refresh diagnostics
        document.getElementById('refreshDiagnostics').addEventListener('click', function() {
            window.location.reload();
        });

        // Fix private key format
        document.getElementById('fixPrivateKeyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const privateKey = document.getElementById('privateKey').value;
            const resultDiv = document.getElementById('fixKeyResult');
            
            if (!privateKey) {
                resultDiv.innerHTML = '<div class="alert alert-danger">Please enter a private key</div>';
                resultDiv.style.display = 'block';
                return;
            }
            
            // Send request to fix the key
            fetch('{{ route("admin.firebase.fix-configuration") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    fix_type: 'private_key_format',
                    private_key: privateKey
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
                resultDiv.style.display = 'block';
            })
            .catch(error => {
                resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                resultDiv.style.display = 'block';
            });
        });

        // Test notification
        document.getElementById('testNotificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const deviceToken = document.getElementById('testDeviceToken').value;
            const resultDiv = document.getElementById('testNotificationResult');
            
            if (!deviceToken) {
                resultDiv.innerHTML = '<div class="alert alert-danger">Please enter a device token</div>';
                resultDiv.style.display = 'block';
                return;
            }
            
            // Send test notification
            fetch('{{ route("admin.firebase.test-notification") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    device_token: deviceToken
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `<div class="alert alert-success">Test notification sent successfully!</div>`;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger">Failed to send test notification: ${data.message}</div>`;
                }
                resultDiv.style.display = 'block';
            })
            .catch(error => {
                resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                resultDiv.style.display = 'block';
            });
        });
    });
</script>
@endsection