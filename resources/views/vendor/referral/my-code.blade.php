@extends('vendor.layouts.app')

@section('title', 'My Referral Code')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'My Referral Code'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0 fw-bold">My Referral Code</h4>
                        <p class="text-muted mb-0">Share your code and earn commissions</p>
                    </div>
                    <a href="{{ route('vendor.referral.earnings') }}" class="btn btn-success rounded-pill">
                        <i class="fas fa-money-bill-wave me-2"></i>View Earnings
                    </a>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                        <div>
                            <h6 class="mb-1 fw-bold">How Referrals Work</h6>
                            <p class="mb-0">Share your referral code with other vendors. When they use it during subscription purchase, they get a <strong>10% discount</strong> and you earn <strong>10% commission</strong>!</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Referral Code Card -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="fas fa-gift me-2 text-primary"></i>Your Referral Code
                                </h5>
                                
                                <!-- Referral Code Display -->
                                <div class="card bg-gradient-primary text-white mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <div class="card-body text-center py-5">
                                        <h6 class="text-white-50 mb-2">Your Code</h6>
                                        <h2 class="display-4 mb-3 fw-bold text-white">{{ $vendor->referral_code }}</h2>
                                        <button class="btn btn-light btn-lg rounded-pill px-4" onclick="copyReferralCode()">
                                            <i class="fas fa-copy me-2"></i>Copy Code
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Share Options -->
                                <div>
                                    <h6 class="mb-3 fw-semibold">Share via:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-success rounded-pill" onclick="shareWhatsApp()">
                                            <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                        </button>
                                        <button class="btn btn-primary rounded-pill" onclick="shareEmail()">
                                            <i class="fas fa-envelope me-1"></i> Email
                                        </button>
                                        <button class="btn btn-info text-white rounded-pill" onclick="shareSMS()">
                                            <i class="fas fa-sms me-1"></i> SMS
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics & Recent Referrals -->
                    <div class="col-lg-6 mb-4">
                        <!-- Total Referrals Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Referrals</h6>
                                        <h2 class="mb-0 fw-bold">{{ $totalReferrals }}</h2>
                                        <small class="text-muted">Vendors who used your code</small>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-users fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Earnings Card -->
                        <div class="card border-0 shadow-sm mb-4 bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Total Earnings</h6>
                                        <h2 class="mb-0 fw-bold text-white">₹{{ number_format($vendor->total_referral_earnings + $vendor->pending_referral_earnings, 2) }}</h2>
                                        <small class="text-white-50">All time earnings</small>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                        <i class="fas fa-money-bill-wave fa-2x text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Referrals -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="fas fa-clock me-2"></i>Recent Referrals
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($recentReferrals->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($recentReferrals as $referral)
                                        <div class="list-group-item px-0 py-3 border-bottom">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">{{ $referral->vendor->store_name }}</h6>
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="fas fa-calendar me-1"></i>{{ $referral->created_at->format('M d, Y') }}
                                                        <span class="ms-2">({{ $referral->created_at->diffForHumans() }})</span>
                                                    </small>
                                                    <span class="badge bg-success">Earned ₹{{ number_format($referral->plan->price * 0.10, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No referrals yet. Start sharing your code!</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyReferralCode() {
    const code = '{{ $vendor->referral_code }}';
    navigator.clipboard.writeText(code).then(() => {
        showAlert('success', 'Referral code copied to clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const input = document.createElement('input');
        input.value = code;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showAlert('success', 'Referral code copied to clipboard!');
    });
}

function shareWhatsApp() {
    const code = '{{ $vendor->referral_code }}';
    const message = `Hey! I'm using this amazing platform for my business. Use my referral code "${code}" when you subscribe and get 10% discount! 🎉`;
    const url = `https://wa.me/?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
}

function shareEmail() {
    const code = '{{ $vendor->referral_code }}';
    const subject = 'Get 10% Discount on Your Subscription!';
    const body = `Hi,\n\nI wanted to share this amazing platform I'm using for my business.\n\nWhen you sign up and subscribe, use my referral code: ${code}\n\nYou'll get 10% discount on your subscription!\n\nCheers,\n{{ $vendor->store_name }}`;
    const url = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = url;
}

function shareSMS() {
    const code = '{{ $vendor->referral_code }}';
    const message = `Use my referral code "${code}" and get 10% discount on your subscription!`;
    const url = `sms:?body=${encodeURIComponent(message)}`;
    window.location.href = url;
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'info' ? 'alert-info' : 'alert-warning';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('.main-content');
    if (container) {
        const firstChild = container.querySelector('.pt-4');
        if (firstChild) {
            firstChild.insertAdjacentHTML('afterbegin', alertHtml);
        }
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}
</script>
@endpush

@push('styles')
<style>
.bg-gradient-primary {
    position: relative;
    overflow: hidden;
}

.bg-gradient-primary::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 3s ease-in-out infinite;
    z-index: 0;
}

.bg-gradient-primary .card-body {
    position: relative;
    z-index: 1;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.list-group-item:last-child {
    border-bottom: 0 !important;
}
</style>
@endpush
