@extends('vendor.layouts.app')

@section('title', 'Coupons')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Coupons'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Coupon Management</h4>
                        <p class="text-muted mb-0">Create and manage discount coupons</p>
                    </div>
                    <button type="button" class="btn btn-theme rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#couponModal">
                        <i class="fas fa-plus me-2"></i>Add Coupon
                    </button>
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
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary rounded-circle p-3">
                                            <i class="fas fa-ticket-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 fw-bold">{{ $coupons->total() }}</h3>
                                        <p class="text-muted mb-0 small">Total Coupons</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success rounded-circle p-3">
                                            <i class="fas fa-check-circle text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 fw-bold">{{ $coupons->where('is_active', true)->count() }}</h3>
                                        <p class="text-muted mb-0 small">Active</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-info rounded-circle p-3">
                                            <i class="fas fa-percent text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 fw-bold">{{ $coupons->where('type', 'percentage')->count() }}</h3>
                                        <p class="text-muted mb-0 small">Percentage</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-warning rounded-circle p-3">
                                            <i class="fas fa-rupee-sign text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 fw-bold">{{ $coupons->where('type', 'fixed')->count() }}</h3>
                                        <p class="text-muted mb-0 small">Fixed Amount</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Coupons Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3">Code</th>
                                        <th class="border-0 py-3">Type</th>
                                        <th class="border-0 py-3">Value</th>
                                        <th class="border-0 py-3">Min. Order</th>
                                        <th class="border-0 py-3">Usage</th>
                                        <th class="border-0 py-3">Validity</th>
                                        <th class="border-0 py-3">Status</th>
                                        <th class="border-0 py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($coupons as $coupon)
                                        <tr>
                                            <td class="px-4">
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 font-monospace">
                                                    {{ $coupon->code }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($coupon->type == 'percentage')
                                                    <span class="badge bg-info bg-opacity-10 text-info">Percentage</span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">Fixed</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($coupon->type == 'percentage')
                                                    {{ $coupon->value }}%
                                                @else
                                                    ₹{{ number_format($coupon->value, 2) }}
                                                @endif
                                            </td>
                                            <td>₹{{ number_format($coupon->min_order_amount ?? 0, 2) }}</td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $coupon->used_count ?? 0 }} / {{ $coupon->max_uses ?? '∞' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($coupon->expires_at)
                                                    @if($coupon->expires_at < now())
                                                        <span class="text-danger small">
                                                            <i class="fas fa-exclamation-circle me-1"></i>Expired
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">
                                                            {{ $coupon->expires_at->format('M d, Y') }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">No expiry</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('vendor.coupons.toggle', $coupon) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                                        @if($coupon->is_active)
                                                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                                                        @endif
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1" 
                                                        onclick="editCoupon({{ json_encode($coupon) }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('vendor.coupons.destroy', $coupon) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this coupon?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-ticket-alt fa-3x mb-3 opacity-25"></i>
                                                    <p class="mb-0">No coupons found</p>
                                                    <small>Create your first coupon to get started</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($coupons->hasPages())
                        <div class="card-footer bg-white border-0 py-3">
                            {{ $coupons->links() }}
                        </div>
                    @endif
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<!-- Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="couponModalLabel">Add Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="couponForm" action="{{ route('vendor.coupons.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="code" class="form-label fw-medium">Coupon Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control rounded-pill rounded-end" id="code" name="code" required style="text-transform: uppercase;">
                            <button type="button" class="btn btn-outline-secondary rounded-pill rounded-start" onclick="generateCode()">
                                <i class="fas fa-random"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label fw-medium">Type <span class="text-danger">*</span></label>
                            <select class="form-select rounded-pill" id="type" name="type" required onchange="updateValueLabel()">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="value" class="form-label fw-medium"><span id="valueLabel">Discount Value</span> <span class="text-danger">*</span></label>
                            <input type="number" class="form-control rounded-pill" id="value" name="value" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="min_order_amount" class="form-label fw-medium">Min. Order Amount</label>
                            <input type="number" class="form-control rounded-pill" id="min_order_amount" name="min_order_amount" step="0.01" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="max_uses" class="form-label fw-medium">Max Uses</label>
                            <input type="number" class="form-control rounded-pill" id="max_uses" name="max_uses" min="1" placeholder="Unlimited">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expires_at" class="form-label fw-medium">Expiry Date</label>
                        <input type="date" class="form-control rounded-pill" id="expires_at" name="expires_at">
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme rounded-pill px-4">
                        <i class="fas fa-save me-2"></i><span id="submitBtnText">Create Coupon</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function generateCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < 8; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('code').value = code;
    }
    
    function updateValueLabel() {
        const type = document.getElementById('type').value;
        const label = document.getElementById('valueLabel');
        if (type === 'percentage') {
            label.textContent = 'Percentage (%)';
        } else {
            label.textContent = 'Amount (₹)';
        }
    }
    
    function editCoupon(coupon) {
        const form = document.getElementById('couponForm');
        const modal = new bootstrap.Modal(document.getElementById('couponModal'));
        
        document.getElementById('couponModalLabel').textContent = 'Edit Coupon';
        document.getElementById('submitBtnText').textContent = 'Update Coupon';
        document.getElementById('formMethod').value = 'PUT';
        form.action = '{{ url("vendor/coupons") }}/' + coupon.id;
        
        document.getElementById('code').value = coupon.code;
        document.getElementById('type').value = coupon.type;
        document.getElementById('value').value = coupon.value;
        document.getElementById('min_order_amount').value = coupon.min_order_amount || '';
        document.getElementById('max_uses').value = coupon.max_uses || '';
        document.getElementById('expires_at').value = coupon.expires_at ? coupon.expires_at.split('T')[0] : '';
        document.getElementById('is_active').checked = coupon.is_active;
        
        updateValueLabel();
        modal.show();
    }
    
    // Reset modal on close
    document.getElementById('couponModal').addEventListener('hidden.bs.modal', function () {
        const form = document.getElementById('couponForm');
        form.reset();
        form.action = '{{ route("vendor.coupons.store") }}';
        document.getElementById('couponModalLabel').textContent = 'Add Coupon';
        document.getElementById('submitBtnText').textContent = 'Create Coupon';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('is_active').checked = true;
        updateValueLabel();
    });
</script>
@endpush