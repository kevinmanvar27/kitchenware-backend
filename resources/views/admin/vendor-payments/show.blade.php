@extends('admin.layouts.app')

@section('title', 'Vendor Payment Details - ' . $vendor->store_name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Vendor Payment Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button -->
                <div class="mb-3">
                    <a href="{{ route('admin.vendor-payments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Vendor Payments
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row">
                    <!-- Vendor Info Card -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <img src="{{ $vendor->store_logo_url }}" class="rounded-circle mb-3" width="100" height="100" alt="{{ $vendor->store_name }}" style="object-fit: cover;">
                                <h5 class="fw-bold mb-1">{{ $vendor->store_name }}</h5>
                                <p class="text-muted mb-2">{{ $vendor->user->name ?? 'N/A' }}</p>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-envelope me-1"></i> {{ $vendor->business_email ?? $vendor->user->email ?? 'N/A' }}
                                </p>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-phone me-1"></i> {{ $vendor->business_phone ?? 'N/A' }}
                                </p>
                                
                                <hr>
                                
                                <!-- Wallet Status -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-medium">Wallet Status</span>
                                    @if($wallet)
                                        @if($wallet->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($wallet->status === 'hold')
                                            <span class="badge bg-warning">On Hold</span>
                                        @else
                                            <span class="badge bg-danger">Suspended</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">No Wallet</span>
                                    @endif
                                </div>
                                
                                <!-- Change Wallet Status -->
                                @if($wallet)
                                <form action="{{ route('admin.vendor-payments.wallet-status', $vendor) }}" method="POST" class="mb-3">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="active" {{ $wallet->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="hold" {{ $wallet->status === 'hold' ? 'selected' : '' }}>On Hold</option>
                                            <option value="suspended" {{ $wallet->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">Update</button>
                                    </div>
                                </form>
                                @endif

                                <!-- RazorpayX Info -->
                                @if($bankAccount && $bankAccount->razorpay_contact_id)
                                <div class="text-start mt-3 p-3 bg-light rounded">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-link me-1"></i> RazorpayX Integration</h6>
                                    <p class="mb-1 small"><strong>Contact ID:</strong> <code>{{ $bankAccount->razorpay_contact_id }}</code></p>
                                    @if($bankAccount->razorpay_fund_account_id)
                                    <p class="mb-0 small"><strong>Fund Account:</strong> <code>{{ $bankAccount->razorpay_fund_account_id }}</code></p>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Wallet Summary Card -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-wallet me-2 text-primary"></i>Wallet Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded text-center">
                                            <p class="text-muted mb-1 small">Total Earned</p>
                                            <h4 class="fw-bold text-primary mb-0">₹{{ number_format($wallet->total_earned ?? 0, 2) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded text-center">
                                            <p class="text-muted mb-1 small">Total Paid</p>
                                            <h4 class="fw-bold text-success mb-0">₹{{ number_format($wallet->total_paid ?? 0, 2) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded text-center">
                                            <p class="text-muted mb-1 small">Pending Amount</p>
                                            <h4 class="fw-bold text-warning mb-0">₹{{ number_format($wallet->pending_amount ?? 0, 2) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded text-center">
                                            <p class="text-muted mb-1 small">Hold Amount</p>
                                            <h4 class="fw-bold text-danger mb-0">₹{{ number_format($wallet->hold_amount ?? 0, 2) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded text-center">
                                            <p class="text-muted mb-1 small">Payable Amount</p>
                                            <h4 class="fw-bold text-info mb-0">₹{{ number_format($wallet->payable_amount ?? 0, 2) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded text-center">
                                            <p class="text-muted mb-1 small">Last Payout</p>
                                            <h6 class="fw-bold mb-0">
                                                {{ $wallet && $wallet->last_payout_at ? $wallet->last_payout_at->format('d M Y') : 'Never' }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Bank Account Card -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-university me-2 text-primary"></i>Bank Account</h5>
                                @if($bankAccount && !$bankAccount->hasFundAccount())
                                <form action="{{ route('admin.vendor-payments.setup-bank', $vendor) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-link me-1"></i> Setup on RazorpayX
                                    </button>
                                </form>
                                @endif
                            </div>
                            <div class="card-body">
                                @if($bankAccount)
                                    <div class="table-responsive">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td class="text-muted" width="40%">Account Holder</td>
                                                <td class="fw-medium">{{ $bankAccount->account_holder_name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Account Number</td>
                                                <td class="fw-medium">{{ $bankAccount->masked_account_number }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">IFSC Code</td>
                                                <td class="fw-medium">{{ $bankAccount->ifsc_code }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Bank Name</td>
                                                <td class="fw-medium">{{ $bankAccount->bank_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Branch</td>
                                                <td class="fw-medium">{{ $bankAccount->branch_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Status</td>
                                                <td>
                                                    @if($bankAccount->is_verified)
                                                        <span class="badge bg-success">Verified</span>
                                                    @else
                                                        <span class="badge bg-warning">Pending Verification</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Fund Account</td>
                                                <td>
                                                    @if($bankAccount->hasFundAccount())
                                                        <span class="badge bg-success">Created</span>
                                                    @else
                                                        <span class="badge bg-secondary">Not Created</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Update Bank Account Form -->
                                    <hr>
                                    <h6 class="fw-bold mb-3">Update Bank Details</h6>
                                    <form action="{{ route('admin.vendor-payments.bank-account', $vendor) }}" method="POST">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small">Account Holder Name</label>
                                                <input type="text" name="account_holder_name" class="form-control form-control-sm" value="{{ $bankAccount->account_holder_name }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Account Number</label>
                                                <input type="text" name="account_number" class="form-control form-control-sm" placeholder="Enter new account number" value="">
                                                <small class="text-muted">Current: {{ $bankAccount->masked_account_number }} (Leave blank to keep existing)</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">IFSC Code</label>
                                                <input type="text" name="ifsc_code" class="form-control form-control-sm" value="{{ $bankAccount->ifsc_code }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Bank Name</label>
                                                <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ $bankAccount->bank_name }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Branch Name</label>
                                                <input type="text" name="branch_name" class="form-control form-control-sm" value="{{ $bankAccount->branch_name }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Account Type</label>
                                                <select name="account_type" class="form-select form-select-sm">
                                                    <option value="savings" {{ ($bankAccount->account_type ?? '') == 'savings' ? 'selected' : '' }}>Savings</option>
                                                    <option value="current" {{ ($bankAccount->account_type ?? '') == 'current' ? 'selected' : '' }}>Current</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-save me-1"></i> Update Bank Account
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-university fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No bank account added yet</p>
                                        
                                        <!-- Add Bank Account Form -->
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#addBankForm">
                                            <i class="fas fa-plus me-1"></i> Add Bank Account
                                        </button>
                                        
                                        <div class="collapse mt-3" id="addBankForm">
                                            <form action="{{ route('admin.vendor-payments.bank-account', $vendor) }}" method="POST" class="text-start">
                                                @csrf
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label small">Account Holder Name</label>
                                                        <input type="text" name="account_holder_name" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Account Number</label>
                                                        <input type="text" name="account_number" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">IFSC Code</label>
                                                        <input type="text" name="ifsc_code" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Bank Name</label>
                                                        <input type="text" name="bank_name" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Branch Name</label>
                                                        <input type="text" name="branch_name" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Account Type</label>
                                                        <select name="account_type" class="form-select form-select-sm">
                                                            <option value="savings">Savings</option>
                                                            <option value="current">Current</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="fas fa-save me-1"></i> Save Bank Account
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Initiate Payout Card -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-paper-plane me-2 text-primary"></i>Initiate Payout</h5>
                            </div>
                            <div class="card-body">
                                @if($wallet && $wallet->payable_amount > 0 && $wallet->status === 'active')
                                    @if($bankAccount)
                                        <form action="{{ route('admin.vendor-payments.payout', $vendor) }}" method="POST">
                                            @csrf
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <strong>Available for Payout:</strong> ₹{{ number_format($wallet->payable_amount, 2) }}
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Payout Amount (₹)</label>
                                                <input type="number" name="amount" class="form-control" step="0.01" min="1" max="{{ $wallet->payable_amount }}" value="{{ $wallet->payable_amount }}" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Payout Mode</label>
                                                <select name="payout_mode" class="form-select" required>
                                                    <option value="NEFT">NEFT (1-2 hours)</option>
                                                    <option value="IMPS">IMPS (Instant)</option>
                                                    <option value="RTGS">RTGS (2-4 hours, min ₹2L)</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Notes (Optional)</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes for this payout..."></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="fas fa-paper-plane me-1"></i> Send Payout
                                            </button>
                                        </form>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Bank account not configured. Please add bank details first.
                                        </div>
                                    @endif
                                @elseif($wallet && $wallet->status !== 'active')
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Wallet is currently <strong>{{ $wallet->status }}</strong>. Payouts are disabled.
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        No pending amount available for payout.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Earnings History -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Earnings History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Invoice</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Commission</th>
                                        <th class="text-end">Net Earning</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($earnings as $index => $earning)
                                        <tr>
                                            <td class="fw-bold">{{ $earnings->firstItem() + $index }}</td>
                                            <td>{{ $earning->created_at->format('d M Y') }}</td>
                                            <td>
                                                @if($earning->invoice)
                                                    <a href="#" class="text-decoration-none">{{ $earning->invoice->invoice_number ?? 'N/A' }}</a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-end">₹{{ number_format($earning->order_amount, 2) }}</td>
                                            <td class="text-end text-danger">-₹{{ number_format($earning->commission_amount, 2) }}</td>
                                            <td class="text-end fw-bold text-success">₹{{ number_format($earning->vendor_earning, 2) }}</td>
                                            <td class="text-center">
                                                @if($earning->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($earning->status === 'confirmed')
                                                    <span class="badge bg-success">Confirmed</span>
                                                @elseif($earning->status === 'paid')
                                                    <span class="badge bg-primary">Paid</span>
                                                @else
                                                    <span class="badge bg-danger">{{ ucfirst($earning->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p class="mb-0">No earnings recorded yet</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $earnings->withQueryString()->links() }}
                        </div>
                    </div>
                </div>

                <!-- Payout History -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Payout History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Payout ID</th>
                                        <th class="text-end">Amount</th>
                                        <th>Mode</th>
                                        <th>UTR</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payouts as $index => $payout)
                                        <tr>
                                            <td class="fw-bold">{{ $payouts->firstItem() + $index }}</td>
                                            <td>{{ $payout->created_at->format('d M Y H:i') }}</td>
                                            <td>
                                                <code class="small">{{ $payout->razorpay_payout_id ?? 'N/A' }}</code>
                                            </td>
                                            <td class="text-end fw-bold">₹{{ number_format($payout->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $payout->payout_mode ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($payout->utr)
                                                    <code class="small">{{ $payout->utr }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($payout->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($payout->status === 'processing')
                                                    <span class="badge bg-info">Processing</span>
                                                @elseif($payout->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($payout->status === 'failed')
                                                    <span class="badge bg-danger">Failed</span>
                                                @elseif($payout->status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @elseif($payout->status === 'reversed')
                                                    <span class="badge bg-warning">Reversed</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($payout->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewPayoutDetails({{ $payout->id }})" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if($payout->razorpay_payout_id && in_array($payout->status, ['pending', 'processing']))
                                                        <form action="{{ route('admin.vendor-payments.sync-payout', $payout) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Sync Status">
                                                                <i class="fas fa-sync"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($payout->status === 'failed' && $payout->retry_count < 3)
                                                        <form action="{{ route('admin.vendor-payments.retry-payout', $payout) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Retry Payout">
                                                                <i class="fas fa-redo"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p class="mb-0">No payouts recorded yet</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $payouts->withQueryString()->links() }}
                        </div>
                    </div>
                </div>

                <!-- Payout Logs -->
                @if($payoutLogs->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-list-alt me-2 text-primary"></i>Recent Payout Logs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Event Type</th>
                                        <th>RazorpayX Status</th>
                                        <th>Payout Status</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payoutLogs as $log)
                                        <tr>
                                            <td class="small">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                            <td><span class="badge bg-secondary">{{ $log->event_type }}</span></td>
                                            <td><span class="badge bg-outline-secondary">{{ $log->razorpay_status ?? '-' }}</span></td>
                                            <td><span class="badge bg-outline-primary">{{ $log->payout->status ?? '-' }}</span></td>
                                            <td class="small">{{ Str::limit($log->message, 50) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>
</div>

<!-- Payout Details Modal -->
<div class="modal fade" id="payoutDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payout Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="payoutDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="syncButtonContainer"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function viewPayoutDetails(payoutId) {
    const modal = new bootstrap.Modal(document.getElementById('payoutDetailsModal'));
    const content = document.getElementById('payoutDetailsContent');
    
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.show();
    
    fetch(`{{ url('admin/vendor-payments/payout') }}/${payoutId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payout) {
                const payout = data.payout;
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Payout Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted">Payout ID</td><td><code>${payout.razorpay_payout_id || 'N/A'}</code></td></tr>
                                <tr><td class="text-muted">Amount</td><td class="fw-bold">₹${parseFloat(payout.amount).toFixed(2)}</td></tr>
                                <tr><td class="text-muted">Mode</td><td>${payout.payout_mode || 'N/A'}</td></tr>
                                <tr><td class="text-muted">Status</td><td><span class="badge bg-${getStatusColor(payout.status)}">${payout.status}</span></td></tr>
                                <tr><td class="text-muted">UTR</td><td><code>${payout.utr || 'N/A'}</code></td></tr>
                                <tr><td class="text-muted">Created</td><td>${new Date(payout.created_at).toLocaleString()}</td></tr>
                                <tr><td class="text-muted">Processed</td><td>${payout.processed_at ? new Date(payout.processed_at).toLocaleString() : 'N/A'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Fund Account Details</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted">Fund Account ID</td><td><code>${payout.fund_account_id || 'N/A'}</code></td></tr>
                                <tr><td class="text-muted">Retry Count</td><td>${payout.retry_count || 0}</td></tr>
                            </table>
                            ${payout.failure_reason ? `
                            <div class="alert alert-danger mt-3">
                                <strong>Failure Reason:</strong><br>
                                ${payout.failure_reason}
                            </div>
                            ` : ''}
                            ${payout.notes ? `
                            <div class="alert alert-info mt-3">
                                <strong>Notes:</strong><br>
                                ${payout.notes}
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                // Add sync button for payouts that can be synced
                const syncContainer = document.getElementById('syncButtonContainer');
                if (['pending', 'processing'].includes(payout.status) && payout.razorpay_payout_id) {
                    syncContainer.innerHTML = `
                        <form action="{{ url('admin/vendor-payments/payout') }}/${payout.id}/sync" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat"></i> Sync Status
                            </button>
                        </form>
                    `;
                } else {
                    syncContainer.innerHTML = '';
                }
            } else {
                content.innerHTML = `<div class="alert alert-danger">
                    <strong>Failed to load payout details</strong><br>
                    ${data.error || 'Unknown error occurred'}
                </div>`;
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error loading payout details. Please try again or contact support if the issue persists.</div>';
            console.error('Payout details fetch error:', error);
        });
}

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'processing': 'info',
        'completed': 'success',
        'failed': 'danger',
        'rejected': 'danger',
        'reversed': 'warning'
    };
    return colors[status] || 'secondary';
}

// Global error handler for fetch requests
window.addEventListener('error', function(event) {
    if (event.message && event.message.includes('fetch')) {
        console.error('Network error detected:', event);
        // Show a toast or notification here if needed
    }
});
</script>
@endsection
