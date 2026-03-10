@extends('vendor.layouts.app')

@section('title', 'Bank Account Details')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Bank Account Details'])
            
            @section('page-title', 'Bank Account Details')
            
            <div class="pt-4 pb-2 mb-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="row">
                    <!-- Bank Account Status -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>Account Status
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($vendor->primaryBankAccount)
                                    <div class="text-center mb-4">
                                        <div class="d-inline-block p-3 rounded-circle bg-light mb-3">
                                            <i class="fas fa-university fa-3x text-primary"></i>
                                        </div>
                                        <h5 class="fw-bold">{{ $vendor->primaryBankAccount->bank_name }}</h5>
                                        <p class="text-muted mb-0">Account ending in {{ substr($vendor->primaryBankAccount->account_number, -4) }}</p>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Verification Status</span>
                                        @if($vendor->primaryBankAccount->is_verified)
                                            <span class="badge bg-success rounded-pill px-3 py-2">Verified</span>
                                        @else
                                            <span class="badge bg-warning rounded-pill px-3 py-2">Pending Verification</span>
                                        @endif
                                    </div>
                                    
                                    @if($vendor->primaryBankAccount->razorpay_fund_account_id)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Payout Status</span>
                                        @if($vendor->primaryBankAccount->fund_account_status === 'created')
                                            <span class="badge bg-success rounded-pill px-3 py-2">Ready</span>
                                        @elseif($vendor->primaryBankAccount->fund_account_status === 'failed')
                                            <span class="badge bg-danger rounded-pill px-3 py-2">Failed</span>
                                        @else
                                            <span class="badge bg-warning rounded-pill px-3 py-2">Pending</span>
                                        @endif
                                    </div>
                                    @endif
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Last Updated</span>
                                        <span class="fw-bold">{{ $vendor->primaryBankAccount->updated_at->format('M d, Y') }}</span>
                                    </div>
                                    
                                    @if($vendor->primaryBankAccount->verified_at)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Verified On</span>
                                        <span class="fw-bold">{{ $vendor->primaryBankAccount->verified_at->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                    
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Bank account verification may take up to 48 hours after submission.
                                    </div>
                                @else
                                    <div class="text-center mb-4">
                                        <div class="d-inline-block p-3 rounded-circle bg-light mb-3">
                                            <i class="fas fa-university fa-3x text-muted"></i>
                                        </div>
                                        <h5 class="fw-bold">No Bank Account</h5>
                                        <p class="text-muted mb-0">Please add your bank account details</p>
                                    </div>
                                    
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        You need to add a bank account to receive payments.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank Account Form -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-university me-2 text-primary"></i>
                                    {{ $vendor->primaryBankAccount ? 'Update Bank Account' : 'Add Bank Account' }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.profile.bank-details.update') }}" method="POST" id="bank-details-form">
                                    @csrf
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="bank_account_holder_name" class="form-label fw-bold">Account Holder Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control rounded-pill px-4" id="bank_account_holder_name" name="bank_account_holder_name" value="{{ old('bank_account_holder_name', $vendor->primaryBankAccount->account_holder_name ?? '') }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="account_type" class="form-label fw-bold">Account Type <span class="text-danger">*</span></label>
                                            <select class="form-select rounded-pill px-4" id="account_type" name="account_type" required>
                                                <option value="">Select Account Type</option>
                                                <option value="savings" {{ old('account_type', $vendor->primaryBankAccount->account_type ?? '') == 'savings' ? 'selected' : '' }}>Savings</option>
                                                <option value="current" {{ old('account_type', $vendor->primaryBankAccount->account_type ?? '') == 'current' ? 'selected' : '' }}>Current</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="bank_account_number" class="form-label fw-bold">Account Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control rounded-pill px-4" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $vendor->primaryBankAccount->account_number ?? '') }}" required>
                                            @if($vendor->primaryBankAccount)
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i> Current account: ••••••{{ substr($vendor->primaryBankAccount->account_number, -4) }}
                                            </div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label for="confirm_account_number" class="form-label fw-bold">Confirm Account Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control rounded-pill px-4" id="confirm_account_number" name="confirm_account_number" value="{{ old('confirm_account_number') }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="bank_ifsc_code" class="form-label fw-bold">IFSC Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control rounded-pill px-4" id="bank_ifsc_code" name="bank_ifsc_code" value="{{ old('bank_ifsc_code', $vendor->primaryBankAccount->ifsc_code ?? '') }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="bank_name" class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control rounded-pill px-4" id="bank_name" name="bank_name" value="{{ old('bank_name', $vendor->primaryBankAccount->bank_name ?? '') }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="branch_name" class="form-label fw-bold">Branch Name</label>
                                        <input type="text" class="form-control rounded-pill px-4" id="branch_name" name="branch_name" value="{{ old('branch_name', $vendor->primaryBankAccount->branch_name ?? '') }}">
                                    </div>
                                    
                                    @if($vendor->primaryBankAccount && $vendor->primaryBankAccount->razorpay_fund_account_id)
                                    <div class="alert alert-warning mb-4">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Important:</strong> Changing your IFSC code will require re-verification of your bank account for automatic payouts.
                                    </div>
                                    @endif
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-theme rounded-pill">
                                            <i class="fas fa-save me-2"></i>{{ $vendor->primaryBankAccount ? 'Update Bank Account' : 'Add Bank Account' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bank-details-form');
        const accountNumber = document.getElementById('bank_account_number');
        const confirmAccountNumber = document.getElementById('confirm_account_number');
        
        // Client-side validation for account number matching
        form.addEventListener('submit', function(e) {
            if (accountNumber.value !== confirmAccountNumber.value) {
                e.preventDefault();
                alert('Account number and confirmation do not match');
                confirmAccountNumber.focus();
            }
        });
        
        // Auto-capitalize IFSC code
        const ifscCode = document.getElementById('bank_ifsc_code');
        ifscCode.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
</script>
@endpush