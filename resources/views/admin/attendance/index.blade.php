@extends('admin.layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Attendance Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">
                                            <i class="fas fa-calendar-check me-2 text-theme"></i>Attendance
                                        </h4>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if(auth()->user()->hasPermission('create_attendance') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.attendance.bulk', ['date' => \Carbon\Carbon::today()->format('Y-m-d')]) }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                            <i class="fas fa-users me-1 me-md-2"></i><span class="d-none d-sm-inline">Mark Bulk Attendance</span><span class="d-sm-none">Bulk</span>
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('viewAny_attendance') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.attendance.report', ['month' => $month, 'year' => $year]) }}" class="btn btn-sm btn-md-normal btn-outline-primary rounded-pill px-3 px-md-4">
                                            <i class="fas fa-chart-bar me-1 me-md-2"></i><span class="d-none d-sm-inline">View Report</span><span class="d-sm-none">Report</span>
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
                                
                                <!-- Filters -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Select Staff Member</label>
                                        <select class="form-select" id="userSelect" onchange="updateCalendar()">
                                            @foreach($staffUsers as $user)
                                                <option value="{{ $user->id }}" {{ $selectedUser && $selectedUser->id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->user_role)) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Month</label>
                                        <select class="form-select" id="monthSelect" onchange="updateCalendar()">
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Year</label>
                                        <select class="form-select" id="yearSelect" onchange="updateCalendar()">
                                            @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button class="btn btn-outline-secondary rounded-pill w-100" onclick="updateCalendar()">
                                            <i class="fas fa-sync-alt me-1"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Summary Cards -->
                                @if($selectedUser)
                                <div class="row mb-4">
                                    <div class="col">
                                        <div class="card border-0 shadow-sm" style="background-color: #d1e7dd;">
                                            <div class="card-body text-center py-3">
                                                <h3 class="mb-0 text-success fw-bold">{{ $summary['present'] }}</h3>
                                                <small class="text-success fw-medium">Present</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card border-0 shadow-sm" style="background-color: #f8d7da;">
                                            <div class="card-body text-center py-3">
                                                <h3 class="mb-0 text-danger fw-bold">{{ $summary['absent'] }}</h3>
                                                <small class="text-danger fw-medium">Absent</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card border-0 shadow-sm" style="background-color: #fff3cd;">
                                            <div class="card-body text-center py-3">
                                                <h3 class="mb-0 text-warning fw-bold">{{ $summary['half_day'] }}</h3>
                                                <small class="text-warning fw-medium">Half Day</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card border-0 shadow-sm" style="background-color: #cff4fc;">
                                            <div class="card-body text-center py-3">
                                                <h3 class="mb-0 text-info fw-bold">{{ $summary['leave'] }}</h3>
                                                <small class="text-info fw-medium">Leave</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card border-0 shadow-sm" style="background-color: #e2e3e5;">
                                            <div class="card-body text-center py-3">
                                                <h3 class="mb-0 text-secondary fw-bold">{{ $summary['holiday'] }}</h3>
                                                <small class="text-secondary fw-medium">Holiday</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Calendar -->
                                <div class="attendance-calendar">
                                    <div class="row text-center fw-bold mb-2">
                                        <div class="col border-bottom py-2">Sun</div>
                                        <div class="col border-bottom py-2">Mon</div>
                                        <div class="col border-bottom py-2">Tue</div>
                                        <div class="col border-bottom py-2">Wed</div>
                                        <div class="col border-bottom py-2">Thu</div>
                                        <div class="col border-bottom py-2">Fri</div>
                                        <div class="col border-bottom py-2">Sat</div>
                                    </div>
                                    
                                    @foreach(array_chunk($calendarDays, 7) as $week)
                                    <div class="row text-center">
                                        @foreach($week as $day)
                                        @php
                                            $canUpdateAttendance = auth()->user()->hasPermission('update_attendance') || auth()->user()->hasPermission('create_attendance') || auth()->user()->isSuperAdmin();
                                        @endphp
                                        <div class="col border p-2 calendar-day {{ !$day['isCurrentMonth'] ? 'bg-light text-muted' : '' }} {{ $day['isToday'] ? 'border-primary border-2' : '' }}"
                                             style="min-height: 80px; cursor: {{ $day['isCurrentMonth'] && !$day['isFuture'] && $selectedUser && $canUpdateAttendance ? 'pointer' : 'default' }};"
                                             @if($day['isCurrentMonth'] && !$day['isFuture'] && $selectedUser && $canUpdateAttendance)
                                             onclick="openAttendanceModal('{{ $day['date']->format('Y-m-d') }}', '{{ $day['attendance'] ? $day['attendance']->status : '' }}')"
                                             @endif>
                                            <div class="d-flex justify-content-between align-items-start">
                                                <span class="fw-medium {{ $day['isToday'] ? 'text-primary' : '' }}">{{ $day['date']->format('d') }}</span>
                                                @if($day['attendance'])
                                                    <span class="badge {{ $day['attendance']->status_badge_class }} rounded-pill">
                                                        {{ substr($day['attendance']->status_label, 0, 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($day['attendance'] && $day['attendance']->check_in)
                                                <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">
                                                    {{ \Carbon\Carbon::parse($day['attendance']->check_in)->format('h:i A') }}
                                                </small>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Legend -->
                                <div class="mt-4 d-flex gap-3 flex-wrap justify-content-center">
                                    <span><span class="badge bg-success rounded-pill">P</span> Present</span>
                                    <span><span class="badge bg-danger rounded-pill">A</span> Absent</span>
                                    <span><span class="badge bg-warning rounded-pill">H</span> Half Day</span>
                                    <span><span class="badge bg-info rounded-pill">L</span> Leave</span>
                                    <span><span class="badge bg-secondary rounded-pill">H</span> Holiday</span>
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

<!-- Attendance Modal -->
@if(auth()->user()->hasPermission('update_attendance') || auth()->user()->hasPermission('create_attendance') || auth()->user()->isSuperAdmin())
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceModalLabel">Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="attendanceForm" method="POST" action="{{ route('admin.attendance.store') }}">
                @csrf
                <input type="hidden" name="user_id" id="modalUserId" value="{{ $selectedUser ? $selectedUser->id : '' }}">
                <input type="hidden" name="date" id="modalDate">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Date</label>
                        <input type="text" class="form-control" id="modalDateDisplay" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Status</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <input type="radio" class="btn-check" name="status" id="statusPresent" value="present" required>
                            <label class="btn btn-outline-success rounded-pill" for="statusPresent">Present</label>
                            
                            <input type="radio" class="btn-check" name="status" id="statusAbsent" value="absent">
                            <label class="btn btn-outline-danger rounded-pill" for="statusAbsent">Absent</label>
                            
                            <input type="radio" class="btn-check" name="status" id="statusHalfDay" value="half_day">
                            <label class="btn btn-outline-warning rounded-pill" for="statusHalfDay">Half Day</label>
                            
                            <input type="radio" class="btn-check" name="status" id="statusLeave" value="leave">
                            <label class="btn btn-outline-info rounded-pill" for="statusLeave">Leave</label>
                            
                            <input type="radio" class="btn-check" name="status" id="statusHoliday" value="holiday">
                            <label class="btn btn-outline-secondary rounded-pill" for="statusHoliday">Holiday</label>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Check In</label>
                            <input type="time" class="form-control" name="check_in" id="modalCheckIn">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Check Out</label>
                            <input type="time" class="form-control" name="check_out" id="modalCheckOut">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Notes</label>
                        <textarea class="form-control" name="notes" id="modalNotes" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme rounded-pill">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('styles')
<style>
    .calendar-day:hover {
        background-color: #f8f9fa;
    }
    .calendar-day.bg-light:hover {
        background-color: #e9ecef !important;
    }
</style>
@endsection

@section('scripts')
<script>
    function updateCalendar() {
        const userId = document.getElementById('userSelect').value;
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearSelect').value;
        
        window.location.href = `{{ route('admin.attendance.index') }}?user_id=${userId}&month=${month}&year=${year}`;
    }
    
    function openAttendanceModal(date, currentStatus) {
        document.getElementById('modalDate').value = date;
        document.getElementById('modalDateDisplay').value = new Date(date).toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        document.getElementById('modalUserId').value = document.getElementById('userSelect').value;
        
        // Reset form
        document.querySelectorAll('input[name="status"]').forEach(el => el.checked = false);
        document.getElementById('modalCheckIn').value = '';
        document.getElementById('modalCheckOut').value = '';
        document.getElementById('modalNotes').value = '';
        
        // Set current status if exists
        if (currentStatus) {
            const statusMap = {
                'present': 'statusPresent',
                'absent': 'statusAbsent',
                'half_day': 'statusHalfDay',
                'leave': 'statusLeave',
                'holiday': 'statusHoliday'
            };
            if (statusMap[currentStatus]) {
                document.getElementById(statusMap[currentStatus]).checked = true;
            }
        }
        
        new bootstrap.Modal(document.getElementById('attendanceModal')).show();
    }
</script>
@endsection
