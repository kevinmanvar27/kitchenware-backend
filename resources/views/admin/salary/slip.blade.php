@extends('admin.layouts.app')

@section('title', 'Salary Slip - ' . $payment->user->name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Salary Slip'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-file-invoice me-2 text-theme"></i>Salary Slip
                                </h4>
                                <div>
                                    <a href="{{ route('admin.salary.payments', ['month' => $payment->month, 'year' => $payment->year]) }}" 
                                       class="btn btn-outline-secondary rounded-pill px-3">
                                        <i class="fas fa-arrow-left me-1"></i> Back
                                    </a>
                                    <a href="{{ route('admin.salary.download-slip', $payment->id) }}" class="btn btn-outline-primary rounded-pill px-3 ms-2">
                                        <i class="fas fa-download me-1"></i> Download PDF
                                    </a>
                                    <button onclick="window.print()" class="btn btn-theme rounded-pill px-3 ms-2">
                                        <i class="fas fa-print me-1"></i> Print
                                    </button>
                                </div>
                            </div>
                            
                            <div class="card-body" id="salarySlip">
                                <!-- Company Header -->
                                <div class="text-center border-bottom pb-3 mb-4">
                                    @if(setting('header_logo'))
                                        <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title') }}" height="60" class="mb-2">
                                    @endif
                                    <h4 class="mb-1">{{ setting('site_title', 'Company Name') }}</h4>
                                    <p class="text-muted mb-0">{{ setting('company_address', '') }}</p>
                                </div>
                                
                                <h5 class="text-center mb-4">SALARY SLIP FOR {{ strtoupper($payment->period) }}</h5>
                                
                                <!-- Employee Details -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td class="text-muted" width="40%">Employee Name:</td>
                                                <td class="fw-medium">{{ $payment->user->name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Email:</td>
                                                <td>{{ $payment->user->email }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Designation:</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $payment->user->user_role)) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td class="text-muted" width="40%">Pay Period:</td>
                                                <td class="fw-medium">{{ $payment->period }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Payment Date:</td>
                                                <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'Pending' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Status:</td>
                                                <td>
                                                    <span class="badge {{ $payment->status_badge_class }} rounded-pill">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Attendance Summary -->
                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">Attendance Summary</h6>
                                        <div class="row text-center">
                                            <div class="col">
                                                <div class="h4 mb-0 text-success">{{ $payment->present_days }}</div>
                                                <small class="text-muted">Present</small>
                                            </div>
                                            <div class="col">
                                                <div class="h4 mb-0 text-danger">{{ $payment->absent_days }}</div>
                                                <small class="text-muted">Absent</small>
                                            </div>
                                            <div class="col">
                                                <div class="h4 mb-0 text-warning">{{ $payment->half_days }}</div>
                                                <small class="text-muted">Half Day</small>
                                            </div>
                                            <div class="col">
                                                <div class="h4 mb-0 text-info">{{ $payment->leave_days }}</div>
                                                <small class="text-muted">Leave</small>
                                            </div>
                                            <div class="col">
                                                <div class="h4 mb-0 text-secondary">{{ $payment->holiday_days }}</div>
                                                <small class="text-muted">Holiday</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Salary Breakdown (for mid-month changes) -->
                                @if(isset($salaryBreakdown) && count($salaryBreakdown) > 1)
                                <div class="card border-info border-0 bg-info text-white mb-4">
                                    <div class="card-header bg-transparent border-0">
                                        <h6 class="fw-bold text-white mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Salary Rate Changes This Month
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Period</th>
                                                        <th>Daily Rate</th>
                                                        <th>Present Days</th>
                                                        <th>Half Days</th>
                                                        <th>Leave/Holiday</th>
                                                        <th class="text-end">Earned</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($salaryBreakdown as $breakdown)
                                                    <tr>
                                                        <td>
                                                            <small>{{ $breakdown['effective_from'] }} - {{ $breakdown['effective_to'] }}</small>
                                                        </td>
                                                        <td>₹{{ number_format($breakdown['daily_rate'], 2) }}</td>
                                                        <td class="text-center">{{ $breakdown['present_days'] }}</td>
                                                        <td class="text-center">{{ $breakdown['half_days'] }}</td>
                                                        <td class="text-center">{{ $breakdown['leave_days'] + $breakdown['holiday_days'] }}</td>
                                                        <td class="text-end fw-medium">₹{{ number_format($breakdown['earned'], 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="5" class="text-end fw-bold">Total Earned:</td>
                                                        <td class="text-end fw-bold">₹{{ number_format($payment->earned_salary, 2) }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Salary Breakdown -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card border-success border-0 bg-success">
                                            <div class="card-header bg-transparent border-0">
                                                <h6 class="fw-bold text-success mb-0">Earnings</h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <td>Current Base Salary</td>
                                                        <td class="text-end">₹{{ number_format($payment->base_salary, 2) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Current Daily Rate</td>
                                                        <td class="text-end">₹{{ number_format($payment->daily_rate, 2) }}</td>
                                                    </tr>
                                                    <tr class="border-top">
                                                        <td>Earned Salary</td>
                                                        <td class="text-end">₹{{ number_format($payment->earned_salary, 2) }}</td>
                                                    </tr>
                                                    @if($payment->bonus > 0)
                                                    <tr>
                                                        <td>Bonus</td>
                                                        <td class="text-end text-success">+₹{{ number_format($payment->bonus, 2) }}</td>
                                                    </tr>
                                                    @endif
                                                    <tr class="border-top">
                                                        <td class="fw-bold">Total Earnings</td>
                                                        <td class="text-end fw-bold">₹{{ number_format($payment->earned_salary + $payment->bonus, 2) }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-danger border-0 bg-danger">
                                            <div class="card-header bg-transparent border-0">
                                                <h6 class="fw-bold text-danger mb-0">Deductions</h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    @if($payment->deductions > 0)
                                                    <tr>
                                                        <td>Deductions</td>
                                                        <td class="text-end">₹{{ number_format($payment->deductions, 2) }}</td>
                                                    </tr>
                                                    @else
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">No deductions</td>
                                                    </tr>
                                                    @endif
                                                    <tr class="border-top">
                                                        <td class="fw-bold">Total Deductions</td>
                                                        <td class="text-end fw-bold">₹{{ number_format($payment->deductions, 2) }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Net Salary -->
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <h5 class="mb-0">Net Salary Payable</h5>
                                            </div>
                                            <div class="col-auto">
                                                <h3 class="mb-0">₹{{ number_format($payment->net_salary, 2) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Payment Details -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td class="text-muted">Amount Paid:</td>
                                                <td class="fw-medium text-success">₹{{ number_format($payment->paid_amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Pending Amount:</td>
                                                <td class="fw-medium text-danger">₹{{ number_format($payment->pending_amount, 2) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            @if($payment->payment_method)
                                            <tr>
                                                <td class="text-muted">Payment Method:</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                            </tr>
                                            @endif
                                            @if($payment->transaction_id)
                                            <tr>
                                                <td class="text-muted">Transaction ID:</td>
                                                <td>{{ $payment->transaction_id }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                                
                                @if($payment->notes)
                                <div class="alert alert-secondary">
                                    <strong>Notes:</strong> {{ $payment->notes }}
                                </div>
                                @endif
                                
                                <!-- Attendance Details -->
                                @if($attendances->count() > 0)
                                <div class="mt-4">
                                    <h6 class="fw-bold mb-3">Attendance Details</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Day</th>
                                                    <th>Status</th>
                                                    <th>Check In</th>
                                                    <th>Check Out</th>
                                                    <th>Rate Applied</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($attendances as $attendance)
                                                @php
                                                    $applicableSalary = \App\Models\Salary::getApplicableSalary($payment->user_id, $attendance->date);
                                                    $rateApplied = 0;
                                                    if ($applicableSalary) {
                                                        if (in_array($attendance->status, ['present', 'leave', 'holiday'])) {
                                                            $rateApplied = $applicableSalary->daily_rate;
                                                        } elseif ($attendance->status === 'half_day') {
                                                            $rateApplied = $applicableSalary->half_day_rate;
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $attendance->date->format('d M Y') }}</td>
                                                    <td>{{ $attendance->date->format('l') }}</td>
                                                    <td>
                                                        <span class="badge {{ $attendance->status_badge_class }} rounded-pill">
                                                            {{ $attendance->status_label }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : '-' }}</td>
                                                    <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : '-' }}</td>
                                                    <td class="text-end">
                                                        @if($rateApplied > 0)
                                                            ₹{{ number_format($rateApplied, 2) }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Footer -->
                                <div class="border-top pt-4 mt-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <p class="text-muted small mb-0">This is a computer generated slip.</p>
                                        </div>
                                    </div>
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
@endsection

@section('styles')
<style>
    @media print {
        .sidebar, .main-content > nav, .card-header .btn, .card-header a, footer {
            display: none !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@endsection
