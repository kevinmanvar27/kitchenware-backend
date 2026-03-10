@extends('admin.layouts.app')

@section('title', 'Salary Management')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Salary Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">
                                            <i class="fas fa-money-bill-wave me-2 text-theme"></i>Salary Management
                                        </h4>
                                        <p class="text-muted mb-0 mt-1 small">Manage staff salaries and rates</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if(auth()->user()->hasPermission('viewAny_staff') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.users.staff') }}" class="btn btn-sm btn-md-normal btn-outline-secondary rounded-pill px-3 px-md-4">
                                            <i class="fas fa-users me-1 me-md-2"></i><span class="d-none d-sm-inline">View Staff</span><span class="d-sm-none">Staff</span>
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('create_salary') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.salary.create') }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                            <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Set/Update Salary</span><span class="d-sm-none">Set</span>
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('viewAny_salary') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.salary.payments') }}" class="btn btn-sm btn-md-normal btn-outline-primary rounded-pill px-3 px-md-4">
                                            <i class="fas fa-file-invoice-dollar me-1 me-md-2"></i><span class="d-none d-sm-inline">Payroll</span>
                                        </a>
                                        @endif
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
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="salaryTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Staff Member</th>
                                                <th>Role</th>
                                                <th>Base Salary</th>
                                                <th>Daily Rate</th>
                                                <th>Half Day Rate</th>
                                                <th>Working Days</th>
                                                <th>Effective From</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($staffUsers as $index => $user)
                                            @php
                                                $activeSalary = $user->salaries->first();
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $user->avatar_url }}" class="rounded-circle me-2" width="40" height="40" alt="{{ $user->name }}">
                                                        <div>
                                                            <div class="fw-medium">{{ $user->name }}</div>
                                                            <small class="text-muted">{{ $user->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                                                        {{ ucfirst(str_replace('_', ' ', $user->user_role)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($activeSalary)
                                                        <span class="fw-bold text-success">₹{{ number_format($activeSalary->base_salary, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">Not Set</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($activeSalary)
                                                        <span>₹{{ number_format($activeSalary->daily_rate, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($activeSalary)
                                                        <span>₹{{ number_format($activeSalary->half_day_rate, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($activeSalary)
                                                        <span>{{ $activeSalary->working_days_per_month }} days</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($activeSalary)
                                                        <span>{{ $activeSalary->effective_from->format('d M Y') }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($activeSalary && $activeSalary->is_active)
                                                        <span class="badge bg-success rounded-pill">Active</span>
                                                    @elseif($activeSalary)
                                                        <span class="badge bg-secondary rounded-pill">Inactive</span>
                                                    @else
                                                        <span class="badge bg-warning rounded-pill">Not Set</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if(auth()->user()->hasPermission('viewAny_salary') || auth()->user()->isSuperAdmin())
                                                        <a href="{{ route('admin.salary.show', $user->id) }}" class="btn btn-outline-info rounded-start-pill px-3">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @endif
                                                        @if(auth()->user()->hasPermission('update_salary') || auth()->user()->hasPermission('create_salary') || auth()->user()->isSuperAdmin())
                                                        <a href="{{ route('admin.salary.create', ['user_id' => $user->id]) }}" class="btn btn-outline-primary {{ (auth()->user()->hasPermission('viewAny_salary') || auth()->user()->isSuperAdmin()) ? '' : 'rounded-start-pill' }} rounded-end-pill px-3">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-users fa-2x mb-3"></i>
                                                        <p class="mb-0">No staff members found</p>
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
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#salaryTable').DataTable({
            "pageLength": 25,
            "ordering": true,
            "searching": true,
            "columnDefs": [
                { "orderable": false, "targets": [9] }
            ]
        });
    });
</script>
@endsection
