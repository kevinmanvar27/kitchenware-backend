@extends('admin.layouts.app')

@section('title', 'Attendance Report')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Attendance Report'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h4 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-chart-bar me-2 text-theme"></i>Attendance Report
                                        </h4>
                                        <p class="text-muted mb-0 mt-1">Monthly attendance summary for all staff</p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                            <i class="fas fa-arrow-left me-2"></i> Back to Calendar
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Filters -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Month</label>
                                        <select class="form-select" id="monthSelect" onchange="updateReport()">
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Year</label>
                                        <select class="form-select" id="yearSelect" onchange="updateReport()">
                                            @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="reportTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Staff Member</th>
                                                <th>Role</th>
                                                <th class="text-center">
                                                    <span class="badge bg-success">Present</span>
                                                </th>
                                                <th class="text-center">
                                                    <span class="badge bg-danger">Absent</span>
                                                </th>
                                                <th class="text-center">
                                                    <span class="badge bg-warning">Half Day</span>
                                                </th>
                                                <th class="text-center">
                                                    <span class="badge bg-info">Leave</span>
                                                </th>
                                                <th class="text-center">
                                                    <span class="badge bg-secondary">Holiday</span>
                                                </th>
                                                <th class="text-center">Total Working</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($staffUsers as $index => $user)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $user->avatar_url }}" class="rounded-circle me-2" width="36" height="36" alt="{{ $user->name }}">
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
                                                <td class="text-center">
                                                    <span class="fw-bold text-success">{{ $user->attendance_summary['present'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="fw-bold text-danger">{{ $user->attendance_summary['absent'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="fw-bold text-warning">{{ $user->attendance_summary['half_day'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="fw-bold text-info">{{ $user->attendance_summary['leave'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="fw-bold text-secondary">{{ $user->attendance_summary['holiday'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                                        {{ $user->attendance_summary['present'] + ($user->attendance_summary['half_day'] * 0.5) }} days
                                                    </span>
                                                </td>
                                                <td>
                                                    @if(auth()->user()->hasPermission('viewAny_attendance') || auth()->user()->isSuperAdmin())
                                                    <a href="{{ route('admin.attendance.index', ['user_id' => $user->id, 'month' => $month, 'year' => $year]) }}" 
                                                       class="btn btn-sm btn-outline-primary rounded-pill">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    @endif
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
        $('#reportTable').DataTable({
            "pageLength": 25,
            "ordering": true,
            "searching": true,
            "columnDefs": [
                { "orderable": false, "targets": [9] }
            ]
        });
    });
    
    function updateReport() {
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearSelect').value;
        window.location.href = `{{ route('admin.attendance.report') }}?month=${month}&year=${year}`;
    }
</script>
@endsection
