@extends('vendor.layouts.app')

@section('title', 'View Coupon')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', [
                'pageTitle' => 'Coupon Details',
                'breadcrumbs' => [
                    'Coupons' => route('vendor.coupons.index'),
                    $coupon->code => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <!-- Coupon Details -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">
                                        <span class="badge bg-dark fs-5 font-monospace me-2">{{ $coupon->code }}</span>
                                        @php $status = $coupon->status; @endphp
                                        @switch($status)
                                            @case('active')
                                                <span class="badge bg-success">Active</span>
                                                @break
                                            @case('inactive')
                                                <span class="badge bg-secondary">Inactive</span>
                                                @break
                                            @case('expired')
                                                <span class="badge bg-danger">Expired</span>
                                                @break
                                            @case('scheduled')
                                                <span class="badge bg-info">Scheduled</span>
                                                @break
                                            @case('exhausted')
                                                <span class="badge bg-warning">Exhausted</span>
                                                @break
                                        @endswitch
                                    </h4>
                                    @if($coupon->description)
                                        <p class="mb-0 text-muted mt-2">{{ $coupon->description }}</p>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('vendor.coupons.edit', $coupon) }}" class="btn btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Discount</h6>
                                        <h3 class="text-success fw-bold">
                                            @if($coupon->discount_type === 'percentage')
                                                {{ $coupon->discount_value }}% OFF
                                            @else
                                                ₹{{ number_format($coupon->discount_value, 2) }} OFF
                                            @endif
                                        </h3>
                                        @if($coupon->discount_type === 'percentage' && $coupon->max_discount_amount)
                                            <small class="text-muted">Max discount: ₹{{ number_format($coupon->max_discount_amount, 2) }}</small>
                                        @endif
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Minimum Order</h6>
                                        <h3 class="fw-bold">
                                            @if($coupon->min_order_amount > 0)
                                                ₹{{ number_format($coupon->min_order_amount, 2) }}
                                            @else
                                                <span class="text-muted">No minimum</span>
                                            @endif
                                        </h3>
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Usage</h6>
                                        <h3 class="fw-bold">
                                            {{ $coupon->usage_count }}
                                            @if($coupon->usage_limit)
                                                <span class="text-muted fs-5">/ {{ $coupon->usage_limit }}</span>
                                            @else
                                                <span class="text-muted fs-5">/ Unlimited</span>
                                            @endif
                                        </h3>
                                        <small class="text-muted">Per user limit: {{ $coupon->per_user_limit }}</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Validity Period</h6>
                                        @if($coupon->valid_from || $coupon->valid_until)
                                            @if($coupon->valid_from)
                                                <p class="mb-1"><strong>From:</strong> {{ $coupon->valid_from->format('d M Y, h:i A') }}</p>
                                            @endif
                                            @if($coupon->valid_until)
                                                <p class="mb-0"><strong>Until:</strong> {{ $coupon->valid_until->format('d M Y, h:i A') }}</p>
                                            @endif
                                        @else
                                            <p class="text-muted mb-0">No expiry date</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Usage History -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-history me-2"></i>Usage History</h5>
                            </div>
                            <div class="card-body">
                                @if($coupon->usages->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>User</th>
                                                    <th>Invoice</th>
                                                    <th>Discount Applied</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($coupon->usages as $usage)
                                                <tr>
                                                    <td>
                                                        @if($usage->user)
                                                            <strong>{{ $usage->user->name }}</strong>
                                                            <br><small class="text-muted">{{ $usage->user->email }}</small>
                                                        @else
                                                            <span class="text-muted">Deleted User</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($usage->proformaInvoice)
                                                            <a href="{{ route('vendor.invoices.show', $usage->proforma_invoice_id) }}">
                                                                #{{ $usage->proformaInvoice->invoice_number }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="text-success fw-bold">₹{{ number_format($usage->discount_applied, 2) }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $usage->created_at->format('d M Y, h:i A') }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">No usage history yet</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-chart-pie me-2"></i>Quick Stats</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $totalSaved = $coupon->usages->sum('discount_applied');
                                @endphp
                                
                                <div class="text-center mb-4">
                                    <h6 class="text-muted">Total Savings Given</h6>
                                    <h2 class="text-success fw-bold">₹{{ number_format($totalSaved, 2) }}</h2>
                                </div>
                                
                                @if($coupon->usage_limit)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Usage Progress</span>
                                        <span class="fw-bold">{{ round(($coupon->usage_count / $coupon->usage_limit) * 100) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        @php $usagePercent = min(100, ($coupon->usage_count / $coupon->usage_limit) * 100); @endphp
                                        <div class="progress-bar bg-theme" role="progressbar" style="width: {{ $usagePercent }}%"></div>
                                    </div>
                                </div>
                                @endif
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Created:</span>
                                    <span>{{ $coupon->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Last Updated:</span>
                                    <span>{{ $coupon->updated_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <a href="{{ route('vendor.coupons.edit', $coupon) }}" class="btn btn-theme w-100 rounded-pill mb-2">
                                    <i class="fas fa-edit me-2"></i>Edit Coupon
                                </a>
                                <a href="{{ route('vendor.coupons.index') }}" class="btn btn-outline-secondary w-100 rounded-pill">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
