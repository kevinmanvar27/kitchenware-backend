@extends('admin.layouts.app')

@section('title', 'Vendor Payments')

@push('styles')
<style>
    /* DataTable Custom Styles */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0;
        font-weight: 500;
        color: #6c757d;
    }
    
    .dataTables_wrapper .dataTables_length select {
        width: auto;
        display: inline-block;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        width: 250px;
        display: inline-block;
    }
    
    .dataTables_wrapper .dataTables_info {
        padding-top: 1rem;
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 1rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin: 0 0.125rem;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
        background: white;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #FF6B00 !important;
        color: white !important;
        border-color: #FF6B00 !important;
        font-weight: 600;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
        background: #FF6B00 !important;
        color: white !important;
        border-color: #FF6B00 !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    table.dataTable tbody tr {
        transition: background-color 0.2s ease;
    }
    
    table.dataTable tbody tr:hover {
        background-color: rgba(255, 107, 0, 0.05);
    }
    
    table.dataTable thead th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        padding: 0.75rem;
    }
    
    table.dataTable tbody td {
        padding: 0.75rem;
        vertical-align: middle;
    }
    
    /* Processing indicator */
    .dataTables_processing {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 1rem 2rem;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        font-weight: 500;
        color: #FF6B00;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            width: 100%;
            max-width: 300px;
        }
        
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            text-align: center;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Vendor Payments'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">Total Vendors</p>
                                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total_vendors']) }}</h4>
                                    </div>
                                    <div class="bg-primary rounded-circle p-3">
                                        <i class="fas fa-store text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">Total Pending</p>
                                        <h4 class="mb-0 fw-bold text-warning">₹{{ number_format($stats['total_pending'], 2) }}</h4>
                                    </div>
                                    <div class="bg-warning rounded-circle p-3">
                                        <i class="fas fa-clock text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">Paid This Month</p>
                                        <h4 class="mb-0 fw-bold text-success">₹{{ number_format($stats['total_paid_this_month'], 2) }}</h4>
                                    </div>
                                    <div class="bg-success rounded-circle p-3">
                                        <i class="fas fa-check-circle text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">RazorpayX Balance</p>
                                        <h4 class="mb-0 fw-bold text-info">₹{{ number_format($stats['razorpayx_balance'], 2) }}</h4>
                                    </div>
                                    <div class="bg-info rounded-circle p-3">
                                        <i class="fas fa-wallet text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div>
                                <h4 class="card-title mb-0 fw-bold h5">Vendor Payment Summary</h4>
                                <p class="mb-0 text-muted small">Manage vendor payouts and earnings</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshBalance()">
                                    <i class="fas fa-sync-alt me-1"></i> Refresh Balance
                                </button>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bulkPayoutModal">
                                    <i class="fas fa-paper-plane me-1"></i> Bulk Payout
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#schedulePayoutModal">
                                    <i class="fas fa-calendar-alt me-1"></i> Schedule Payouts
                                </button>
                                <a href="{{ route('admin.vendor-payments.earnings-report') }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-chart-line me-1"></i> Earnings Report
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
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
                        
                        @if($payoutSchedule && $payoutSchedule->enabled && $payoutSchedule->scheduled_at)
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-calendar-check me-2"></i>
                                <strong>Auto Payout Active:</strong> Scheduled for {{ $payoutSchedule->scheduled_at->format('M d, Y h:i A') }} via {{ $payoutSchedule->payout_mode ?? 'N/A' }}
                                @if($payoutSchedule->last_run_at)
                                    <span class="ms-2 text-muted">| Last executed: {{ $payoutSchedule->last_run_at->format('M d, Y h:i A') }}</span>
                                @endif
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Filters -->
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Search vendor..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="wallet_status" class="form-select">
                                    <option value="all">All Wallet Status</option>
                                    <option value="active" {{ request('wallet_status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="hold" {{ request('wallet_status') == 'hold' ? 'selected' : '' }}>On Hold</option>
                                    <option value="suspended" {{ request('wallet_status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="bank_status" class="form-select">
                                    <option value="">All Bank Status</option>
                                    <option value="created" {{ request('bank_status') == 'created' ? 'selected' : '' }}>Fund Account Created</option>
                                    <option value="pending" {{ request('bank_status') == 'pending' ? 'selected' : '' }}>Pending Setup</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-theme w-100">Filter</button>
                            </div>
                        </form>

                        <!-- Vendors Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="vendorPaymentsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check me-2">
                                                    <input class="form-check-input" type="checkbox" id="selectAllVendors" onchange="toggleSelectAll()">
                                                </div>
                                                <span data-bs-toggle="tooltip" title="Select vendors for bulk payout">
                                                    <i class="fas fa-paper-plane text-muted small"></i>
                                                </span>
                                            </div>
                                        </th>
                                        <th>#</th>
                                        <th>Vendor</th>
                                        <th>Email</th>
                                        <th class="text-end">Total Earned</th>
                                        <th class="text-end">Total Paid</th>
                                        <th class="text-end">Pending</th>
                                        <th class="text-center">Bank Status</th>
                                        <th>Last Payout</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendors as $index => $vendor)
                                        @php
                                            $wallet = $vendor->wallet;
                                            $bankAccount = $vendor->primaryBankAccount;
                                            $lastPayout = $vendor->payouts->first();
                                            // Modified to be less restrictive - only requiring wallet and bank account
                                            $canPayout = $wallet && $wallet->payable_amount > 0 && $wallet->status === 'active' && $bankAccount;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input vendor-checkbox" type="checkbox" 
                                                        value="{{ $vendor->id }}" 
                                                        data-amount="{{ $wallet->payable_amount ?? 0 }}" 
                                                        data-name="{{ $vendor->store_name }}"
                                                        {{ !$canPayout ? 'disabled' : '' }}
                                                        title="{{ !$canPayout ? 'Vendor not eligible for payout' : 'Select for bulk payout' }}">
                                                </div>
                                            </td>
                                            <td class="fw-bold">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $vendor->store_logo_url }}" class="rounded-circle me-2" width="40" height="40" alt="{{ $vendor->store_name }}" style="object-fit: cover;">
                                                    <div>
                                                        <div class="fw-medium">{{ $vendor->store_name }}</div>
                                                        <small class="text-muted">{{ $vendor->user->name ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $vendor->business_email ?? $vendor->user->email ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-end fw-medium">
                                                ₹{{ number_format($wallet->total_earned ?? 0, 2) }}
                                            </td>
                                            <td class="text-end text-success fw-medium">
                                                ₹{{ number_format($wallet->total_paid ?? 0, 2) }}
                                            </td>
                                            <td class="text-end text-warning fw-bold">
                                                ₹{{ number_format($wallet->pending_amount ?? 0, 2) }}
                                            </td>
                                            <td class="text-center">
                                                @if($bankAccount && $bankAccount->hasFundAccount())
                                                    <span class="badge bg-success rounded-pill">
                                                        <i class="fas fa-check me-1"></i>Created
                                                    </span>
                                                @elseif($bankAccount)
                                                    <span class="badge bg-warning rounded-pill">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary rounded-pill">
                                                        <i class="fas fa-times me-1"></i>No Account
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($wallet && $wallet->last_payout_at)
                                                    <span class="text-muted small">{{ $wallet->last_payout_at->format('d M Y') }}</span>
                                                @else
                                                    <span class="text-muted small">Never</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($wallet)
                                                    @if($wallet->status === 'active')
                                                        <span class="badge bg-success rounded-pill">Active</span>
                                                    @elseif($wallet->status === 'hold')
                                                        <span class="badge bg-warning rounded-pill">On Hold</span>
                                                    @else
                                                        <span class="badge bg-danger rounded-pill">Suspended</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary rounded-pill">No Wallet</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.vendor-payments.show', $vendor) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($wallet && $wallet->pending_amount > 0 && $wallet->status === 'active' && $bankAccount)
                                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payoutModal{{ $vendor->id }}" title="Pay Now">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Quick Payout Modal -->
                                        @if($wallet && $wallet->pending_amount > 0 && $bankAccount)
                                        <div class="modal fade" id="payoutModal{{ $vendor->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.vendor-payments.payout', $vendor) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Initiate Payout - {{ $vendor->store_name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-info">
                                                                <strong>Available Balance:</strong> ₹{{ number_format($wallet->payable_amount, 2) }}
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
                                                                <textarea name="notes" class="form-control" rows="2"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fas fa-paper-plane me-1"></i> Send Payout
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p>No vendors found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bulk Payout Modal -->
<div class="modal fade" id="bulkPayoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.vendor-payments.bulk-payout') }}" method="POST" id="bulkPayoutForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Payout</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Select vendors</strong> from the list to include in the bulk payout.
                        <hr class="my-2">
                        <small>
                            <strong>Note:</strong> Only vendors with active wallets, available balance, and configured bank accounts are eligible for payouts. Ineligible vendors will have disabled checkboxes.
                        </small>
                    </div>
                    
                    <div id="selectedVendorsContainer" class="mb-3">
                        <p class="text-center text-muted">No vendors selected</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payout Mode</label>
                        <select name="payout_mode" class="form-select" required>
                            <option value="NEFT" {{ ($payoutSchedule->payout_mode ?? '') == 'NEFT' ? 'selected' : '' }}>NEFT (1-2 hours)</option>
                            <option value="IMPS" {{ ($payoutSchedule->payout_mode ?? '') == 'IMPS' ? 'selected' : '' }}>IMPS (Instant)</option>
                            <option value="RTGS" {{ ($payoutSchedule->payout_mode ?? '') == 'RTGS' ? 'selected' : '' }}>RTGS (2-4 hours, min ₹2L)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add notes for all payouts..."></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="payFullAmount" name="pay_full_amount" value="1" checked>
                        <label class="form-check-label" for="payFullAmount">
                            Pay full available amount to each vendor
                        </label>
                    </div>
                    
                    <div id="bulkPayoutSummary" class="alert alert-success" style="display: none;">
                        <p class="mb-1"><strong>Summary:</strong></p>
                        <p class="mb-1">Total Vendors: <span id="totalVendorsCount">0</span></p>
                        <p class="mb-0">Total Amount: ₹<span id="totalPayoutAmount">0.00</span></p>
                    </div>
                    
                    <div id="vendorInputsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="bulkPayoutBtn" disabled>
                        <i class="fas fa-paper-plane me-1"></i> Process Bulk Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Payout Modal -->
<div class="modal fade" id="schedulePayoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.vendor-payments.schedule') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Payouts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Schedule automatic payout for all eligible vendors. The system will automatically pay out the full pending amount to each vendor at the specified date and time.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Schedule Date & Time</label>
                        <input type="datetime-local" 
                               name="scheduled_at" 
                               id="scheduledAt" 
                               class="form-control" 
                               value="{{ old('scheduled_at', $payoutSchedule && $payoutSchedule->scheduled_at ? $payoutSchedule->scheduled_at->format('Y-m-d\TH:i') : '') }}" 
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               required>
                        <small class="text-muted">Defaults to last day of current month at 11:59 PM. Select your preferred date and time (must be in the future).</small>
                        @error('scheduled_at')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    @if($payoutSchedule && $payoutSchedule->last_run_at)
                    <div class="alert alert-secondary mb-3">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Last Executed:</strong> {{ $payoutSchedule->last_run_at->format('M d, Y h:i A') }}
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label">Payout Mode</label>
                        <select name="payout_mode" id="payoutMode" class="form-select" required>
                            <option value="">-- Select Payout Mode --</option>
                            <option value="NEFT" {{ old('payout_mode', $payoutSchedule->payout_mode ?? '') == 'NEFT' ? 'selected' : '' }}>NEFT (1-2 hours)</option>
                            <option value="IMPS" {{ old('payout_mode', $payoutSchedule->payout_mode ?? '') == 'IMPS' ? 'selected' : '' }}>IMPS (Instant)</option>
                            <option value="RTGS" {{ old('payout_mode', $payoutSchedule->payout_mode ?? '') == 'RTGS' ? 'selected' : '' }}>RTGS (2-4 hours, min ₹2L)</option>
                            <option value="UPI" {{ old('payout_mode', $payoutSchedule->payout_mode ?? '') == 'UPI' ? 'selected' : '' }}>UPI (Instant)</option>
                        </select>
                        <small class="text-muted">Choose the payment mode for automatic payouts.</small>
                        @error('payout_mode')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="enableSchedule" 
                               name="enabled" 
                               value="1" 
                               {{ old('enabled', $payoutSchedule->enabled ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="enableSchedule">
                            Enable automatic payouts
                        </label>
                        <small class="form-text text-muted d-block">Check this to activate automatic payout processing at the scheduled time.</small>
                    </div>
                    
                    @if($payoutSchedule && $payoutSchedule->enabled)
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Auto Payout Active:</strong> Scheduled for {{ $payoutSchedule->scheduled_at ? $payoutSchedule->scheduled_at->format('M d, Y h:i A') : 'Not set' }} via {{ $payoutSchedule->payout_mode }}
                    </div>
                    @elseif($payoutSchedule && $payoutSchedule->scheduled_at)
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Schedule saved for <strong>{{ $payoutSchedule->scheduled_at->format('M d, Y h:i A') }}</strong> but <strong>not enabled</strong>. Check the box above to activate.
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Set minimum datetime to current time and default to last day of month
document.addEventListener('DOMContentLoaded', function() {
    const scheduledAtInput = document.getElementById('scheduledAt');
    if (scheduledAtInput) {
        // Function to get last day of current month at 11:59 PM
        function getLastDayOfMonth() {
            const now = new Date();
            const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0); // Last day of current month
            lastDay.setHours(23, 59, 0, 0);
            
            // If last day has passed, use next month's last day
            if (lastDay <= now) {
                const nextMonthLastDay = new Date(now.getFullYear(), now.getMonth() + 2, 0);
                nextMonthLastDay.setHours(23, 59, 0, 0);
                return nextMonthLastDay;
            }
            
            return lastDay;
        }
        
        // Set default value to last day of month if no value exists
        if (!scheduledAtInput.value || scheduledAtInput.value === '') {
            const lastDay = getLastDayOfMonth();
            const year = lastDay.getFullYear();
            const month = String(lastDay.getMonth() + 1).padStart(2, '0');
            const day = String(lastDay.getDate()).padStart(2, '0');
            const hours = String(lastDay.getHours()).padStart(2, '0');
            const minutes = String(lastDay.getMinutes()).padStart(2, '0');
            scheduledAtInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
        
        // Update minimum datetime every minute to keep it current
        function updateMinDateTime() {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 5); // Set minimum to 5 minutes from now
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            scheduledAtInput.setAttribute('min', minDateTime);
        }
        
        updateMinDateTime();
        setInterval(updateMinDateTime, 60000); // Update every minute
        
        // Validate on change - prevent past dates
        scheduledAtInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const now = new Date();
            const minDate = new Date(now.getTime() + 5 * 60000); // 5 minutes from now
            
            if (selectedDate < minDate) {
                alert('⚠️ Please select a future date and time (at least 5 minutes from now).\n\nPast dates and times cannot be selected.');
                
                // Reset to last day of month
                const lastDay = getLastDayOfMonth();
                const year = lastDay.getFullYear();
                const month = String(lastDay.getMonth() + 1).padStart(2, '0');
                const day = String(lastDay.getDate()).padStart(2, '0');
                const hours = String(lastDay.getHours()).padStart(2, '0');
                const minutes = String(lastDay.getMinutes()).padStart(2, '0');
                this.value = `${year}-${month}-${day}T${hours}:${minutes}`;
            }
        });
        
        // Validate on input (real-time)
        scheduledAtInput.addEventListener('input', function() {
            const selectedDate = new Date(this.value);
            const now = new Date();
            const minDate = new Date(now.getTime() + 5 * 60000);
            
            if (selectedDate < minDate) {
                this.setCustomValidity('Please select a date and time at least 5 minutes in the future');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Prevent manual entry of past dates
        scheduledAtInput.addEventListener('blur', function() {
            const selectedDate = new Date(this.value);
            const now = new Date();
            
            if (this.value && selectedDate < now) {
                alert('⚠️ Past dates are not allowed. Resetting to last day of month.');
                const lastDay = getLastDayOfMonth();
                const year = lastDay.getFullYear();
                const month = String(lastDay.getMonth() + 1).padStart(2, '0');
                const day = String(lastDay.getDate()).padStart(2, '0');
                const hours = String(lastDay.getHours()).padStart(2, '0');
                const minutes = String(lastDay.getMinutes()).padStart(2, '0');
                this.value = `${year}-${month}-${day}T${hours}:${minutes}`;
            }
        });
    }
    
    // Show helpful tooltip when modal opens
    const scheduleModal = document.getElementById('schedulePayoutModal');
    if (scheduleModal) {
        scheduleModal.addEventListener('show.bs.modal', function() {
            const input = document.getElementById('scheduledAt');
            const payoutModeSelect = document.getElementById('payoutMode');
            const enableCheckbox = document.getElementById('enableSchedule');
            
            // Debug: Log current values
            console.log('Modal opening - Current values:', {
                scheduledAt: input ? input.value : 'N/A',
                payoutMode: payoutModeSelect ? payoutModeSelect.value : 'N/A',
                enabled: enableCheckbox ? enableCheckbox.checked : 'N/A'
            });
            
            // Only set default if no value exists (not from database)
            if (input) {
                // Check if the current value is the default or empty
                const currentValue = input.value;
                const hasExistingSchedule = {{ $payoutSchedule && $payoutSchedule->scheduled_at ? 'true' : 'false' }};
                
                // Only set to last day of month if no existing schedule
                if (!hasExistingSchedule && (!currentValue || currentValue === '')) {
                    const lastDay = getLastDayOfMonth();
                    const year = lastDay.getFullYear();
                    const month = String(lastDay.getMonth() + 1).padStart(2, '0');
                    const day = String(lastDay.getDate()).padStart(2, '0');
                    const hours = String(lastDay.getHours()).padStart(2, '0');
                    const minutes = String(lastDay.getMinutes()).padStart(2, '0');
                    input.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                    console.log('Set default date:', input.value);
                }
            }
            
            // Ensure payout mode is properly selected from database
            if (payoutModeSelect) {
                const savedPayoutMode = '{{ $payoutSchedule->payout_mode ?? "" }}';
                console.log('Saved payout mode from server:', savedPayoutMode);
                
                // Only set the value if there's a saved value from database
                if (savedPayoutMode && payoutModeSelect.value !== savedPayoutMode) {
                    payoutModeSelect.value = savedPayoutMode;
                    console.log('Set payout mode to:', savedPayoutMode);
                }
                // Don't set a default - let the user choose or keep the existing selection
            }
            
            console.log('Modal opened - Final values:', {
                scheduledAt: input ? input.value : 'N/A',
                payoutMode: payoutModeSelect ? payoutModeSelect.value : 'N/A',
                enabled: enableCheckbox ? enableCheckbox.checked : 'N/A'
            });
        });
    }
    
    function getLastDayOfMonth() {
        const now = new Date();
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        lastDay.setHours(23, 59, 0, 0);
        
        if (lastDay <= now) {
            const nextMonthLastDay = new Date(now.getFullYear(), now.getMonth() + 2, 0);
            nextMonthLastDay.setHours(23, 59, 0, 0);
            return nextMonthLastDay;
        }
        
        return lastDay;
    }
});

function refreshBalance() {
    fetch('{{ route("admin.vendor-payments.balance") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('RazorpayX Balance: ₹' + parseFloat(data.balance).toFixed(2));
            } else {
                alert('Failed to fetch balance: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error fetching balance');
            console.error(error);
        });
}

// Bulk payout functionality
let selectedVendors = [];

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.vendor-checkbox:not([disabled])');
    const selectAll = document.getElementById('selectAllVendors');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedVendors();
}

document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all vendor checkboxes
    const checkboxes = document.querySelectorAll('.vendor-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedVendors);
    });
    
    // Initialize bulk payout modal
    const bulkPayoutModal = document.getElementById('bulkPayoutModal');
    bulkPayoutModal.addEventListener('show.bs.modal', updateSelectedVendors);
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function updateSelectedVendors() {
    selectedVendors = [];
    const checkboxes = document.querySelectorAll('.vendor-checkbox:checked');
    const container = document.getElementById('selectedVendorsContainer');
    const vendorInputs = document.getElementById('vendorInputsContainer');
    const bulkPayoutBtn = document.getElementById('bulkPayoutBtn');
    const summary = document.getElementById('bulkPayoutSummary');
    const totalVendorsCount = document.getElementById('totalVendorsCount');
    const totalPayoutAmount = document.getElementById('totalPayoutAmount');
    
    vendorInputs.innerHTML = '';
    
    checkboxes.forEach(checkbox => {
        selectedVendors.push({
            id: checkbox.value,
            name: checkbox.dataset.name,
            amount: parseFloat(checkbox.dataset.amount)
        });
        
        // Create hidden inputs for each vendor
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'vendors[]';
        input.value = checkbox.value;
        vendorInputs.appendChild(input);
    });
    
    if (selectedVendors.length > 0) {
        let html = '<div class="table-responsive"><table class="table table-sm table-striped">';
        html += '<thead><tr><th>Vendor</th><th class="text-end">Amount (₹)</th></tr></thead><tbody>';
        
        let totalAmount = 0;
        selectedVendors.forEach(vendor => {
            html += `<tr>
                <td>${vendor.name}</td>
                <td class="text-end">₹${vendor.amount.toFixed(2)}</td>
            </tr>`;
            totalAmount += vendor.amount;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
        
        // Update summary
        totalVendorsCount.textContent = selectedVendors.length;
        totalPayoutAmount.textContent = totalAmount.toFixed(2);
        summary.style.display = 'block';
        
        // Enable the button
        bulkPayoutBtn.disabled = false;
    } else {
        container.innerHTML = '<p class="text-center text-muted">No vendors selected</p>';
        summary.style.display = 'none';
        bulkPayoutBtn.disabled = true;
    }
}
</script>

@push('scripts')
<script>
    $(document).ready(function() {
        // Check if jQuery and DataTables are loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }
        
        if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables is not loaded!');
            return;
        }
        
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#vendorPaymentsTable')) {
            $('#vendorPaymentsTable').DataTable().destroy();
        }
        
        // Initialize DataTable
        try {
            var table = $('#vendorPaymentsTable').DataTable({
                "processing": true,
                "order": [[6, "desc"]], // Sort by Pending amount (descending)
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "language": {
                    "search": "Search:",
                    "searchPlaceholder": "Search vendors...",
                    "lengthMenu": "Show _MENU_ vendors per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ vendors",
                    "infoEmpty": "No vendors available",
                    "infoFiltered": "(filtered from _MAX_ total vendors)",
                    "zeroRecords": "No matching vendors found",
                    "emptyTable": "No vendors available",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing..."
                },
                "columnDefs": [
                    { 
                        "orderable": false, 
                        "targets": [0, 10] // Disable sorting on checkbox and Actions columns
                    },
                    { 
                        "searchable": false, 
                        "targets": [0, 10] // Disable search on checkbox and Actions columns
                    },
                    {
                        // Custom rendering for # column to show row number
                        "targets": 1,
                        "render": function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    }
                ],
                "responsive": true,
                "autoWidth": false,
                "stateSave": false,
                "dom": '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                "drawCallback": function(settings) {
                    // Re-initialize Bootstrap tooltips after table draw
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                    
                    // Re-initialize Bootstrap dropdowns
                    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
                    dropdownElementList.map(function (dropdownToggleEl) {
                        return new bootstrap.Dropdown(dropdownToggleEl);
                    });
                    
                    // Re-attach checkbox event listeners after redraw
                    const checkboxes = document.querySelectorAll('.vendor-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.removeEventListener('change', updateSelectedVendors);
                        checkbox.addEventListener('change', updateSelectedVendors);
                    });
                }
            });

            // Custom styling for DataTable elements
            $('.dataTables_length select').addClass('form-select form-select-sm');
            $('.dataTables_filter input').addClass('form-control form-control-sm').attr('placeholder', 'Search vendors...');
            
            console.log('DataTable initialized successfully');
            
        } catch (error) {
            console.error('Error initializing DataTable:', error);
        }
    });
</script>
@endpush
@endsection
