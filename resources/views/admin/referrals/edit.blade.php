@extends('admin.layouts.app')

@section('title', 'Edit Referral Code')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Edit Referral Code',
                'breadcrumbs' => [
                    'Referrals' => route('admin.referrals.index'),
                    'Edit' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-edit me-2 text-theme"></i>Edit Referral Code
                                    </h5>
                                    <span class="badge bg-dark fs-6 font-monospace">{{ $referral->referral_code }}</span>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <form action="{{ route('admin.referrals.update', $referral) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                id="name" name="name" value="{{ old('name', $referral->name) }}" 
                                                placeholder="Enter referral name" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="phone_number" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                                id="phone_number" name="phone_number" value="{{ old('phone_number', $referral->phone_number) }}" 
                                                placeholder="Enter phone number">
                                            @error('phone_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="amount" class="form-label">Amount (₹)</label>
                                            <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" 
                                                id="amount" name="amount" value="{{ old('amount', $referral->amount) }}" 
                                                placeholder="Enter amount">
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Referral reward/commission amount</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Referral Code</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control font-monospace" value="{{ $referral->referral_code }}" readonly>
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $referral->referral_code }}')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Referral code cannot be changed</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                                <option value="active" {{ old('status', $referral->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status', $referral->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="payment_status" class="form-label">Payment Status <span class="text-danger">*</span></label>
                                            <select class="form-select @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status" required>
                                                <option value="pending" {{ old('payment_status', $referral->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="paid" {{ old('payment_status', $referral->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                                <option value="cancelled" {{ old('payment_status', $referral->payment_status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                            @error('payment_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Track referral payment status</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                            <i class="fas fa-arrow-left me-2"></i>Back
                                        </a>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.referrals.users', $referral) }}" class="btn btn-outline-info rounded-pill px-4">
                                                <i class="fas fa-users me-2"></i>View Users
                                            </a>
                                            <button type="submit" class="btn btn-theme rounded-pill px-4">
                                                <i class="fas fa-save me-2"></i>Update Referral
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Referral code copied to clipboard!');
    });
}
</script>
@endsection
