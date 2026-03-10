@extends('vendor.layouts.app')

@section('title', 'Bulk Attendance')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Bulk Attendance'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-users me-2 text-theme"></i>Bulk Attendance
                                        </h4>
                                        <p class="text-muted mb-0 small">Mark attendance for all staff members at once</p>
                                    </div>
                                    <a href="{{ route('vendor.attendance.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
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
                                
                                <!-- Date Selector -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Select Date</label>
                                        <input type="date" class="form-control rounded-pill" id="dateSelector" 
                                               value="{{ $date->format('Y-m-d') }}" 
                                               max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                               onchange="changeDate()">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill rounded-end" onclick="markAllPresent()">
                                                <i class="fas fa-check-circle me-1"></i>All Present
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary rounded-pill rounded-start" onclick="markAllAbsent()">
                                                <i class="fas fa-times-circle me-1"></i>All Absent
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <form action="{{ route('vendor.attendance.store-bulk') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-0 px-4 py-3">Staff Member</th>
                                                    <th class="border-0 py-3 text-center">Status</th>
                                                    <th class="border-0 py-3">Check In</th>
                                                    <th class="border-0 py-3">Check Out</th>
                                                    <th class="border-0 py-3">Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($staffUsers as $index => $user)
                                                    @php
                                                        $attendance = $attendances[$user->id] ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td class="px-4">
                                                            <input type="hidden" name="attendance[{{ $index }}][user_id]" value="{{ $user->id }}">
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
                                                            <div class="d-flex gap-1 justify-content-center flex-wrap">
                                                                <input type="radio" class="btn-check status-radio" name="attendance[{{ $index }}][status]" id="present{{ $index }}" value="present" {{ ($attendance && $attendance->status == 'present') ? 'checked' : '' }} required>
                                                                <label class="btn btn-sm btn-outline-success rounded-pill" for="present{{ $index }}" title="Present">P</label>
                                                                
                                                                <input type="radio" class="btn-check status-radio" name="attendance[{{ $index }}][status]" id="absent{{ $index }}" value="absent" {{ ($attendance && $attendance->status == 'absent') ? 'checked' : '' }}>
                                                                <label class="btn btn-sm btn-outline-danger rounded-pill" for="absent{{ $index }}" title="Absent">A</label>
                                                                
                                                                <input type="radio" class="btn-check status-radio" name="attendance[{{ $index }}][status]" id="halfday{{ $index }}" value="half_day" {{ ($attendance && $attendance->status == 'half_day') ? 'checked' : '' }}>
                                                                <label class="btn btn-sm btn-outline-warning rounded-pill" for="halfday{{ $index }}" title="Half Day">H</label>
                                                                
                                                                <input type="radio" class="btn-check status-radio" name="attendance[{{ $index }}][status]" id="leave{{ $index }}" value="leave" {{ ($attendance && $attendance->status == 'leave') ? 'checked' : '' }}>
                                                                <label class="btn btn-sm btn-outline-info rounded-pill" for="leave{{ $index }}" title="Leave">L</label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="time" class="form-control form-control-sm rounded-pill" 
                                                                   name="attendance[{{ $index }}][check_in]" 
                                                                   value="{{ $attendance && $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '' }}">
                                                        </td>
                                                        <td>
                                                            <input type="time" class="form-control form-control-sm rounded-pill" 
                                                                   name="attendance[{{ $index }}][check_out]" 
                                                                   value="{{ $attendance && $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '' }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm rounded-pill" 
                                                                   name="attendance[{{ $index }}][notes]" 
                                                                   value="{{ $attendance->notes ?? '' }}"
                                                                   placeholder="Optional notes">
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-5">
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
                                    
                                    @if($staffUsers->count() > 0)
                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-theme rounded-pill px-5">
                                                <i class="fas fa-save me-2"></i>Save Attendance
                                            </button>
                                        </div>
                                    @endif
                                </form>
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
    function changeDate() {
        const date = document.getElementById('dateSelector').value;
        window.location.href = `{{ route('vendor.attendance.bulk') }}?date=${date}`;
    }
    
    function markAllPresent() {
        document.querySelectorAll('input[value="present"].status-radio').forEach(el => el.checked = true);
    }
    
    function markAllAbsent() {
        document.querySelectorAll('input[value="absent"].status-radio').forEach(el => el.checked = true);
    }
</script>
@endpush