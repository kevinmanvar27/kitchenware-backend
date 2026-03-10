@extends('admin.layouts.app')

@section('title', 'Salary Details - ' . $user->name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Salary Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- User Info Card with Gradient -->
                <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="position-relative">
                                    <img src="{{ $user->avatar_url }}" class="rounded-circle border border-3 border-white shadow" width="90" height="90" alt="{{ $user->name }}" style="object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 20px; height: 20px;"></span>
                                </div>
                            </div>
                            <div class="col">
                                <h3 class="mb-1 text-white fw-bold">{{ $user->name }}</h3>
                                <p class="mb-2 text-white opacity-75">
                                    <i class="fas fa-envelope me-2"></i>{{ $user->email }}
                                </p>
                                <span class="badge bg-white text-dark rounded-pill px-3 py-2">
                                    <i class="fas fa-user-tag me-1"></i>{{ ucfirst(str_replace('_', ' ', $user->user_role)) }}
                                </span>
                            </div>
                            <div class="col-auto">
                                @if(auth()->user()->hasPermission('update_salary') || auth()->user()->hasPermission('create_salary') || auth()->user()->isSuperAdmin())
                                <a href="{{ route('admin.salary.create', ['user_id' => $user->id]) }}" class="btn btn-light rounded-pill px-4 py-2 shadow-sm">
                                    <i class="fas fa-edit me-2"></i> Update Salary
                                </a>
                                @endif
                                <a href="{{ route('admin.salary.index') }}" class="btn btn-outline-light rounded-pill px-4 py-2 ms-2">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Current Salary Card -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-money-bill-wave me-2 text-success"></i>Current Salary Structure
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($activeSalary)
                                    <!-- Salary Overview Cards -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                <div class="card-body text-center text-white p-4">
                                                    <div class="mb-2">
                                                        <i class="fas fa-wallet fa-2x opacity-75"></i>
                                                    </div>
                                                    <h2 class="mb-0 fw-bold">₹{{ number_format($activeSalary->base_salary, 0) }}</h2>
                                                    <small class="opacity-75">Monthly Salary</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                                <div class="card-body text-center text-white p-4">
                                                    <div class="mb-2">
                                                        <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                                                    </div>
                                                    <h2 class="mb-0 fw-bold">₹{{ number_format($activeSalary->daily_rate, 0) }}</h2>
                                                    <small class="opacity-75">Daily Rate</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                                <div class="card-body text-center text-white p-4">
                                                    <div class="mb-2">
                                                        <i class="fas fa-clock fa-2x opacity-75"></i>
                                                    </div>
                                                    <h2 class="mb-0 fw-bold">₹{{ number_format($activeSalary->half_day_rate, 0) }}</h2>
                                                    <small class="opacity-75">Half Day Rate</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Detailed Information -->
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-calendar-alt text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Working Days/Month</small>
                                                        <h5 class="mb-0 fw-bold">{{ $activeSalary->working_days_per_month }} days</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-calendar-check text-success"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Effective From</small>
                                                        <h5 class="mb-0 fw-bold">{{ $activeSalary->effective_from->format('d M Y') }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-check-circle text-info"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Status</small>
                                                        <h5 class="mb-0"><span class="badge bg-success rounded-pill">Active</span></h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-calculator text-warning"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Hourly Rate (Approx)</small>
                                                        <h5 class="mb-0 fw-bold">₹{{ number_format($activeSalary->daily_rate / 8, 2) }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($activeSalary->notes)
                                    <div class="alert alert-info border-0 mt-4 mb-0" role="alert">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle fa-lg"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <strong>Notes:</strong>
                                                <p class="mb-0 mt-1">{{ $activeSalary->notes }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @else
                                    <div class="text-center py-5">
                                        <div class="mb-4">
                                            <i class="fas fa-exclamation-circle fa-4x text-warning opacity-50"></i>
                                        </div>
                                        <h4 class="mb-2">No Active Salary</h4>
                                        <p class="text-muted mb-4">Salary has not been set for this staff member.</p>
                                        @if(auth()->user()->hasPermission('create_salary') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.salary.create', ['user_id' => $user->id]) }}" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-plus me-2"></i> Set Salary Now
                                        </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Salary History Timeline -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-history me-2 text-info"></i>Salary History
                                </h5>
                            </div>
                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                @if($salaryHistory->count() > 0)
                                    <div class="timeline position-relative">
                                        @foreach($salaryHistory as $index => $salary)
                                        <div class="timeline-item position-relative ps-4 pb-4 {{ !$loop->last ? 'border-start border-2 border-secondary' : '' }}" style="margin-left: 8px;">
                                            <!-- Timeline dot -->
                                            <div class="position-absolute bg-white" style="left: -9px; top: 5px;">
                                                <div class="rounded-circle {{ $salary->is_active ? 'bg-success' : 'bg-secondary' }} d-flex align-items-center justify-content-center" style="width: 18px; height: 18px;">
                                                    @if($salary->is_active)
                                                        <i class="fas fa-check text-white" style="font-size: 8px;"></i>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Timeline content -->
                                            <div class="card border {{ $salary->is_active ? 'border-success shadow-sm' : 'border-light' }} mb-0">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-success">₹{{ number_format($salary->base_salary, 2) }}</h6>
                                                            <small class="text-muted">per month</small>
                                                        </div>
                                                        @if($salary->is_active)
                                                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-2 py-1">
                                                                <i class="fas fa-check-circle me-1"></i>Current
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="small mb-2">
                                                        <div class="text-muted mb-1">
                                                            <i class="fas fa-calendar-plus me-1 text-primary"></i>
                                                            <strong>From:</strong> {{ $salary->effective_from->format('d M Y') }}
                                                        </div>
                                                        @if($salary->effective_to)
                                                            <div class="text-muted">
                                                                <i class="fas fa-calendar-minus me-1 text-danger"></i>
                                                                <strong>To:</strong> {{ $salary->effective_to->format('d M Y') }}
                                                            </div>
                                                        @else
                                                            <div class="text-success">
                                                                <i class="fas fa-infinity me-1"></i>
                                                                <strong>Ongoing</strong>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="border-top pt-2 mt-2">
                                                        <div class="row g-2 small">
                                                            <div class="col-6">
                                                                <span class="text-muted">Daily:</span>
                                                                <strong class="d-block">₹{{ number_format($salary->daily_rate, 2) }}</strong>
                                                            </div>
                                                            <div class="col-6">
                                                                <span class="text-muted">Half Day:</span>
                                                                <strong class="d-block">₹{{ number_format($salary->half_day_rate, 2) }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    @if($salary->notes)
                                                        <div class="small text-muted mt-2 pt-2 border-top">
                                                            <i class="fas fa-sticky-note me-1"></i>
                                                            {{ Str::limit($salary->notes, 60) }}
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Show percentage change -->
                                                    @if($index < $salaryHistory->count() - 1)
                                                        @php
                                                            $previousSalary = $salaryHistory[$index + 1];
                                                            $change = $salary->base_salary - $previousSalary->base_salary;
                                                            $changePercent = ($change / $previousSalary->base_salary) * 100;
                                                        @endphp
                                                        @if($change != 0)
                                                            <div class="small mt-2 pt-2 border-top">
                                                                @if($change > 0)
                                                                    <span class="badge bg-success-subtle text-success-emphasis">
                                                                        <i class="fas fa-arrow-up me-1"></i>
                                                                        +{{ number_format($changePercent, 1) }}% Hike
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger-subtle text-danger-emphasis">
                                                                        <i class="fas fa-arrow-down me-1"></i>
                                                                        {{ number_format($changePercent, 1) }}% Decrease
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-history fa-3x text-muted opacity-50 mb-3"></i>
                                        <p class="text-muted mb-0">No salary history available</p>
                                    </div>
                                @endif
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
