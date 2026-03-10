@extends('admin.layouts.app')

@section('title', 'Bulk Attendance')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Bulk Attendance'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h4 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-users me-2 text-theme"></i>Mark Bulk Attendance
                                        </h4>
                                        <p class="text-muted mb-0 mt-1">Mark attendance for all staff members at once</p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                            <i class="fas fa-arrow-left me-2"></i> Back to Calendar
                                        </a>
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
                                
                                @if(!(auth()->user()->hasPermission('create_attendance') || auth()->user()->isSuperAdmin()))
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>You don't have permission to mark bulk attendance.
                                    </div>
                                @else
                                <!-- Date Selector -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Select Date</label>
                                        <input type="date" class="form-control" id="dateSelect" value="{{ $date->format('Y-m-d') }}" 
                                               max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                               onchange="window.location.href='{{ route('admin.attendance.bulk') }}?date=' + this.value">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="setAllStatus('present')">
                                                All Present
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="setAllStatus('absent')">
                                                All Absent
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setAllStatus('holiday')">
                                                All Holiday
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <form method="POST" action="{{ route('admin.attendance.store-bulk') }}">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Staff Member</th>
                                                    <th>Role</th>
                                                    <th>Status</th>
                                                    <th>Check In</th>
                                                    <th>Check Out</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($staffUsers as $index => $user)
                                                @php
                                                    $existingAttendance = $attendances[$user->id] ?? null;
                                                @endphp
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <input type="hidden" name="attendance[{{ $index }}][user_id]" value="{{ $user->id }}">
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
                                                    <td>
                                                        <select class="form-select form-select-sm status-select" name="attendance[{{ $index }}][status]" style="width: 120px;">
                                                            <option value="present" {{ $existingAttendance && $existingAttendance->status == 'present' ? 'selected' : '' }}>Present</option>
                                                            <option value="absent" {{ $existingAttendance && $existingAttendance->status == 'absent' ? 'selected' : '' }}>Absent</option>
                                                            <option value="half_day" {{ $existingAttendance && $existingAttendance->status == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                                            <option value="leave" {{ $existingAttendance && $existingAttendance->status == 'leave' ? 'selected' : '' }}>Leave</option>
                                                            <option value="holiday" {{ $existingAttendance && $existingAttendance->status == 'holiday' ? 'selected' : '' }}>Holiday</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="time" class="form-control form-control-sm" name="attendance[{{ $index }}][check_in]" 
                                                               value="{{ $existingAttendance && $existingAttendance->check_in ? \Carbon\Carbon::parse($existingAttendance->check_in)->format('H:i') : '' }}"
                                                               style="width: 110px;">
                                                    </td>
                                                    <td>
                                                        <input type="time" class="form-control form-control-sm" name="attendance[{{ $index }}][check_out]"
                                                               value="{{ $existingAttendance && $existingAttendance->check_out ? \Carbon\Carbon::parse($existingAttendance->check_out)->format('H:i') : '' }}"
                                                               style="width: 110px;">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" name="attendance[{{ $index }}][notes]"
                                                               value="{{ $existingAttendance ? $existingAttendance->notes : '' }}"
                                                               placeholder="Optional notes..." style="width: 150px;">
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-theme rounded-pill px-5">
                                            <i class="fas fa-save me-2"></i> Save All Attendance
                                        </button>
                                    </div>
                                </form>
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

@section('scripts')
<script>
    function setAllStatus(status) {
        document.querySelectorAll('.status-select').forEach(select => {
            select.value = status;
        });
    }
</script>
@endsection
