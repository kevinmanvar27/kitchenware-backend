@extends('vendor.layouts.app')

@section('title', 'Salary Management')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Salary Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Salary Management</h4>
                        <p class="text-muted mb-0">Manage staff salaries and payments</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vendor.salary.create') }}" class="btn btn-theme rounded-pill px-4">
                            <i class="fas fa-plus me-2"></i> Set Salary
                        </a>
                        <a href="{{ route('vendor.salary.payments') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Payroll
                        </a>
                    </div>
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
                                            <i class="fas fa-users text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 fw-bold">{{ $staffUsers->count() }}</h3>
                                        <p class="text-muted mb-0 small">Total Staff</p>
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
                                            <i class="fas fa-rupee-sign text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 fw-bold">₹{{ number_format($totalPaidThisMonth ?? 0, 0) }}</h3>
                                        <p class="text-muted mb-0 small">Paid This Month</p>
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
                                            <i class="fas fa-clock text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 fw-bold">₹{{ number_format($totalPending ?? 0, 0) }}</h3>
                                        <p class="text-muted mb-0 small">Pending</p>
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
                                            <i class="fas fa-wallet text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0 fw-bold">₹{{ number_format($totalMonthlyBudget ?? 0, 0) }}</h3>
                                        <p class="text-muted mb-0 small">Monthly Budget</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Staff List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold">Staff Members</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3">Staff</th>
                                        <th class="border-0 py-3">Role</th>
                                        <th class="border-0 py-3">Monthly Salary</th>
                                        <th class="border-0 py-3">Daily Rate</th>
                                        <th class="border-0 py-3">Status</th>
                                        <th class="border-0 py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staffUsers as $user)
                                        @php
                                            $activeSalary = $user->salaries->first();
                                        @endphp
                                        <tr>
                                            <td class="px-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 45px; height: 45px;">
                                                        <span class="text-primary fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-medium">{{ $user->name }}</h6>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $user->user_role ?? 'Staff')) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($activeSalary)
                                                    <span class="fw-medium">₹{{ number_format($activeSalary->base_salary, 0) }}</span>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($activeSalary)
                                                    <span class="text-muted">₹{{ number_format($activeSalary->daily_rate, 2) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($activeSalary)
                                                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">No Salary</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('vendor.salary.show', $user->id) }}" 
                                                       class="btn btn-outline-primary rounded-start-pill">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('vendor.salary.create', ['user_id' => $user->id]) }}" 
                                                       class="btn btn-outline-secondary rounded-end-pill">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                                                    <p class="mb-0">No staff members found</p>
                                                    <small>Add staff members to manage their salaries</small>
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
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection