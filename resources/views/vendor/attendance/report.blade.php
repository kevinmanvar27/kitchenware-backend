@extends('vendor.layouts.app')

@section('title', 'Attendance Report')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Attendance Report'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-chart-bar me-2 text-theme"></i>Attendance Report
                                        </h4>
                                        <p class="text-muted mb-0 small">Monthly attendance summary for all staff</p>
                                    </div>
                                    <a href="{{ route('vendor.attendance.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Month/Year Selector -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Month</label>
                                        <select class="form-select rounded-pill" id="monthSelect" onchange="updateReport()">
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Year</label>
                                        <select class="form-select rounded-pill" id="yearSelect" onchange="updateReport()">
                                            @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Report Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 px-4 py-3">Staff Member</th>
                                                <th class="border-0 py-3 text-center">
                                                    <span class="badge bg-success bg-opacity-10 text-success">Present</span>
                                                </th>
                                                <th class="border-0 py-3 text-center">
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">Absent</span>
                                                </th>
                                                <th class="border-0 py-3 text-center">
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">Half Day</span>
                                                </th>
                                                <th class="border-0 py-3 text-center">
                                                    <span class="badge bg-info bg-opacity-10 text-info">Leave</span>
                                                </th>
                                                <th class="border-0 py-3 text-center">
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Holiday</span>
                                                </th>
                                                <th class="border-0 py-3 text-center">Working Days</th>
                                                <th class="border-0 py-3 text-center">Attendance %</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($staffUsers as $user)
                                                @php
                                                    $summary = $user->attendance_summary;
                                                    $totalDays = $summary['present'] + $summary['absent'] + $summary['half_day'] + $summary['leave'];
                                                    $workingDays = $summary['present'] + ($summary['half_day'] * 0.5);
                                                    $percentage = $totalDays > 0 ? round(($workingDays / $totalDays) * 100, 1) : 0;
                                                @endphp
                                                <tr>
                                                    <td class="px-4">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                                 style="width: 40px; height: 40px;">
                                                                <span class="text-primary fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-medium">{{ $user->name }}</h6>
                                                                <small class="text-muted">{{ $user->email }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-bold text-success">{{ $summary['present'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-bold text-danger">{{ $summary['absent'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-bold text-warning">{{ $summary['half_day'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-bold text-info">{{ $summary['leave'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-bold text-secondary">{{ $summary['holiday'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-bold">{{ $summary['total_working'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($percentage >= 90)
                                                            <span class="badge bg-success rounded-pill px-3">{{ $percentage }}%</span>
                                                        @elseif($percentage >= 75)
                                                            <span class="badge bg-warning rounded-pill px-3">{{ $percentage }}%</span>
                                                        @else
                                                            <span class="badge bg-danger rounded-pill px-3">{{ $percentage }}%</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
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
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateReport() {
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearSelect').value;
        window.location.href = `{{ route('vendor.attendance.report') }}?month=${month}&year=${year}`;
    }
</script>
@endpush