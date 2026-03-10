@extends('admin.layouts.app')

@section('title', 'Payroll - ' . date('F Y', mktime(0, 0, 0, $month, 1, $year)))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Payroll Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h4 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-file-invoice-dollar me-2 text-theme"></i>Payroll
                                        </h4>
                                        <p class="text-muted mb-0 mt-1">{{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="d-flex justify-content-end gap-2 flex-wrap align-items-center">
                                            <select class="form-select" style="width: 150px;" id="monthSelect" onchange="updatePayroll()">
                                                @for($m = 1; $m <= 12; $m++)
                                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                    </option>
                                                @endfor
                                            </select>
                                            <select class="form-select" style="width: 100px;" id="yearSelect" onchange="updatePayroll()">
                                                @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                            @if(auth()->user()->hasPermission('update_salary') || auth()->user()->isSuperAdmin())
                                            <a href="{{ route('admin.salary.payments', ['month' => $month, 'year' => $year, 'refresh' => 1]) }}" 
                                               class="btn btn-outline-secondary rounded-pill">
                                                <i class="fas fa-sync-alt me-1"></i> Recalculate All
                                            </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('viewAny_salary') || auth()->user()->hasPermission('create_salary') || auth()->user()->isSuperAdmin())
                                            <a href="{{ route('admin.salary.index') }}" class="btn btn-outline-primary rounded-pill">
                                                <i class="fas fa-cog me-1"></i> Manage Salaries
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <!-- Summary Cards -->
                                <div class="row mb-4">
                                    <div class="col-md-2">
                                        <div class="card bg-primary border-0">
                                            <div class="card-body text-center py-3">
                                                <h5 class="mb-0 text-white">₹{{ number_format($totals['total_earned'], 2) }}</h5>
                                                <small class="text-white">Total Earned</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card bg-danger border-0">
                                            <div class="card-body text-center py-3">
                                                <h5 class="mb-0 text-white">₹{{ number_format($totals['total_deductions'], 2) }}</h5>
                                                <small class="text-white">Deductions</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card bg-success border-0">
                                            <div class="card-body text-center py-3">
                                                <h5 class="mb-0 text-white">₹{{ number_format($totals['total_bonus'], 2) }}</h5>
                                                <small class="text-white">Bonus</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card bg-info border-0">
                                            <div class="card-body text-center py-3">
                                                <h5 class="mb-0 text-white">₹{{ number_format($totals['total_net'], 2) }}</h5>
                                                <small class="text-white">Net Payable</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card bg-success border-0">
                                            <div class="card-body text-center py-3">
                                                <h5 class="mb-0 text-white">₹{{ number_format($totals['total_paid'], 2) }}</h5>
                                                <small class="text-white">Paid</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card bg-warning border-0">
                                            <div class="card-body text-center py-3">
                                                <h5 class="mb-0 text-white">₹{{ number_format($totals['total_pending'], 2) }}</h5>
                                                <small class="text-white">Pending</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="payrollTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Staff Member</th>
                                                <th class="text-center">Present</th>
                                                <th class="text-center">Absent</th>
                                                <th class="text-center">Half Day</th>
                                                <th class="text-center">Leave</th>
                                                <th class="text-end">Earned</th>
                                                <th class="text-end">Deductions</th>
                                                <th class="text-end">Bonus</th>
                                                <th class="text-end">Net Salary</th>
                                                <th class="text-end">Paid</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payments as $payment)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $payment->user->avatar_url }}" class="rounded-circle me-2" width="36" height="36" alt="{{ $payment->user->name }}">
                                                        <div>
                                                            <div class="fw-medium">{{ $payment->user->name }}</div>
                                                            <small class="text-muted">₹{{ number_format($payment->daily_rate, 2) }}/day</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success-subtle text-success-emphasis">{{ $payment->present_days }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger-subtle text-danger-emphasis">{{ $payment->absent_days }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis">{{ $payment->half_days }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info-subtle text-info-emphasis">{{ $payment->leave_days }}</span>
                                                </td>
                                                <td class="text-end">₹{{ number_format($payment->earned_salary, 2) }}</td>
                                                <td class="text-end text-danger">
                                                    @if($payment->deductions > 0)
                                                        -₹{{ number_format($payment->deductions, 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-end text-success">
                                                    @if($payment->bonus > 0)
                                                        +₹{{ number_format($payment->bonus, 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-end fw-bold">₹{{ number_format($payment->net_salary, 2) }}</td>
                                                <td class="text-end">₹{{ number_format($payment->paid_amount, 2) }}</td>
                                                <td>
                                                    <span class="badge {{ $payment->status_badge_class }} rounded-pill">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if(auth()->user()->hasPermission('update_salary') || auth()->user()->isSuperAdmin())
                                                        <button type="button" class="btn btn-outline-primary rounded-start-pill" 
                                                                onclick="openPaymentModal({{ json_encode($payment) }})">
                                                            <i class="fas fa-money-bill"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary"
                                                                onclick="openAdjustmentModal({{ json_encode($payment) }})">
                                                            <i class="fas fa-sliders-h"></i>
                                                        </button>
                                                        @endif
                                                        @if(auth()->user()->hasPermission('viewAny_salary') || auth()->user()->isSuperAdmin())
                                                        <a href="{{ route('admin.salary.slip', $payment->id) }}" class="btn btn-outline-info {{ (auth()->user()->hasPermission('update_salary') || auth()->user()->isSuperAdmin()) ? '' : 'rounded-start-pill' }} rounded-end-pill">
                                                            <i class="fas fa-file-alt"></i>
                                                        </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Net Salary:</span>
                            <strong id="modalNetSalary">₹0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Already Paid:</span>
                            <strong id="modalAlreadyPaid">₹0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <span>Pending:</span>
                            <strong id="modalPending" class="text-danger">₹0.00</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="paid_amount" id="paymentAmount" step="0.01" min="0" required>
                        </div>
                        <div class="form-text">
                            <a href="#" onclick="setFullPayment()">Pay full pending amount</a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Payment Method</label>
                        <select class="form-select" name="payment_method">
                            <option value="">Select method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Transaction ID</label>
                        <input type="text" class="form-control" name="transaction_id" placeholder="Optional">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme rounded-pill">Process Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Adjustment Modal -->
<div class="modal fade" id="adjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Salary Adjustments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustmentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Earned Salary:</span>
                            <strong id="adjEarnedSalary">₹0.00</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Deductions</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="deductions" id="adjDeductions" step="0.01" min="0" value="0">
                        </div>
                        <small class="text-muted">Late fees, penalties, advances, etc.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Bonus</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="bonus" id="adjBonus" step="0.01" min="0" value="0">
                        </div>
                        <small class="text-muted">Performance bonus, incentives, etc.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Notes</label>
                        <textarea class="form-control" name="notes" id="adjNotes" rows="2" placeholder="Reason for adjustments..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <div class="d-flex justify-content-between">
                            <span>New Net Salary:</span>
                            <strong id="adjNewNet">₹0.00</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme rounded-pill">Save Adjustments</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentPayment = null;
    
    function updatePayroll() {
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearSelect').value;
        window.location.href = `{{ route('admin.salary.payments') }}?month=${month}&year=${year}`;
    }
    
    function openPaymentModal(payment) {
        currentPayment = payment;
        document.getElementById('paymentForm').action = `/admin/salary/payments/${payment.id}/process`;
        document.getElementById('modalNetSalary').textContent = '₹' + parseFloat(payment.net_salary).toFixed(2);
        document.getElementById('modalAlreadyPaid').textContent = '₹' + parseFloat(payment.paid_amount).toFixed(2);
        document.getElementById('modalPending').textContent = '₹' + parseFloat(payment.pending_amount).toFixed(2);
        document.getElementById('paymentAmount').value = parseFloat(payment.pending_amount).toFixed(2);
        
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    }
    
    function setFullPayment() {
        if (currentPayment) {
            document.getElementById('paymentAmount').value = parseFloat(currentPayment.pending_amount).toFixed(2);
        }
    }
    
    function openAdjustmentModal(payment) {
        currentPayment = payment;
        document.getElementById('adjustmentForm').action = `/admin/salary/payments/${payment.id}/adjustments`;
        document.getElementById('adjEarnedSalary').textContent = '₹' + parseFloat(payment.earned_salary).toFixed(2);
        document.getElementById('adjDeductions').value = parseFloat(payment.deductions).toFixed(2);
        document.getElementById('adjBonus').value = parseFloat(payment.bonus).toFixed(2);
        document.getElementById('adjNotes').value = payment.notes || '';
        
        calculateNewNet();
        
        new bootstrap.Modal(document.getElementById('adjustmentModal')).show();
    }
    
    function calculateNewNet() {
        if (currentPayment) {
            const earned = parseFloat(currentPayment.earned_salary);
            const deductions = parseFloat(document.getElementById('adjDeductions').value) || 0;
            const bonus = parseFloat(document.getElementById('adjBonus').value) || 0;
            const newNet = earned - deductions + bonus;
            document.getElementById('adjNewNet').textContent = '₹' + newNet.toFixed(2);
        }
    }
    
    document.getElementById('adjDeductions').addEventListener('input', calculateNewNet);
    document.getElementById('adjBonus').addEventListener('input', calculateNewNet);
    
    $(document).ready(function() {
        $('#payrollTable').DataTable({
            "pageLength": 25,
            "ordering": true,
            "searching": true,
            "columnDefs": [
                { "orderable": false, "targets": [11] }
            ]
        });
    });
</script>
@endsection
