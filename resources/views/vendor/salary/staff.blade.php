@extends('vendor.layouts.app')

@section('title', 'Staff Salary - ' . $user->name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Staff Salary'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px;">
                            <span class="text-primary fw-bold fs-4">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                            <p class="text-muted mb-0">{{ $user->email }} | {{ ucfirst($staffMember->role ?? 'Staff') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('vendor.salary.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="row g-4">
                    <!-- Salary Configuration -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Salary Configuration</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.salary.config.store', $user) }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="base_salary" class="form-label fw-medium">Base Salary</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="base_salary" name="base_salary" 
                                                   value="{{ $staffMember->salary ?? 0 }}" step="0.01" min="0">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="payment_type" class="form-label fw-medium">Payment Type</label>
                                        <select class="form-select" id="payment_type" name="payment_type">
                                            <option value="monthly" {{ ($staffMember->payment_type ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="weekly" {{ ($staffMember->payment_type ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="daily" {{ ($staffMember->payment_type ?? '') == 'daily' ? 'selected' : '' }}>Daily</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-theme rounded-pill px-4 w-100">
                                        <i class="fas fa-save me-2"></i>Save Configuration
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Process Payment -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Process Payment</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.salary.process', $user) }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="amount" class="form-label fw-medium">Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="amount" name="amount" 
                                                   value="{{ $staffMember->salary ?? 0 }}" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="month" class="form-label fw-medium">For Month</label>
                                        <input type="month" class="form-control" id="month" name="month" 
                                               value="{{ now()->format('Y-m') }}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label fw-medium">Payment Method</label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="upi">UPI</option>
                                            <option value="cheque">Cheque</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label fw-medium">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success rounded-pill px-4 w-100">
                                        <i class="fas fa-money-bill-wave me-2"></i>Process Payment
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <span class="text-muted">Total Paid (This Year)</span>
                                    <span class="fw-bold">₹{{ number_format($totalPaidThisYear, 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <span class="text-muted">Total Paid (This Month)</span>
                                    <span class="fw-bold">₹{{ number_format($totalPaidThisMonth, 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <span class="text-muted">Last Payment</span>
                                    <span class="fw-medium">
                                        @if($lastPayment)
                                            {{ $lastPayment->created_at->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Total Payments</span>
                                    <span class="fw-bold">{{ $payments->total() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment History -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold">Payment History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3">Date</th>
                                        <th class="border-0 py-3">Month</th>
                                        <th class="border-0 py-3">Amount</th>
                                        <th class="border-0 py-3">Method</th>
                                        <th class="border-0 py-3">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        <tr>
                                            <td class="px-4">{{ $payment->created_at->format('M d, Y') }}</td>
                                            <td>{{ $payment->month ?? $payment->created_at->format('F Y') }}</td>
                                            <td class="fw-medium">₹{{ number_format($payment->amount, 0) }}</td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Cash')) }}
                                                </span>
                                            </td>
                                            <td class="text-muted">{{ $payment->notes ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
                                                    <p class="mb-0">No payment history</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($payments->hasPages())
                        <div class="card-footer bg-white border-0 py-3">
                            {{ $payments->links() }}
                        </div>
                    @endif
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection