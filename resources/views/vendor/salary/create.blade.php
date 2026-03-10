@extends('vendor.layouts.app')

@section('title', 'Set/Update Salary')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Set/Update Salary'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-money-bill-wave me-2 text-theme"></i>
                                            {{ $user ? 'Update Salary for ' . $user->name : 'Set New Salary' }}
                                        </h4>
                                        <p class="text-muted mb-0 mt-1">Configure salary rates and working days</p>
                                    </div>
                                    <a href="{{ route('vendor.salary.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        <i class="fas fa-arrow-left me-2"></i> Back
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <form method="POST" action="{{ route('vendor.salary.store') }}">
                                    @csrf
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Select Staff Member <span class="text-danger">*</span></label>
                                        <select class="form-select" name="user_id" id="userSelect" required onchange="loadUserSalary(this.value)">
                                            <option value="">-- Select Staff Member --</option>
                                            @foreach($staffUsers as $staffUser)
                                                <option value="{{ $staffUser->id }}" 
                                                        data-current-salary="{{ $staffUser->salaries->first()->base_salary ?? 0 }}"
                                                        {{ $user && $user->id == $staffUser->id ? 'selected' : '' }}>
                                                    {{ $staffUser->name }} ({{ ucfirst(str_replace('_', ' ', $staffUser->user_role ?? 'Staff')) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <!-- Current Salary Display -->
                                    <div id="currentSalarySection" class="mb-4" style="display: {{ $user && $user->salaries->first() ? 'block' : 'none' }};">
                                        <div class="alert alert-info border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <div class="row align-items-center text-white">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                                                            <i class="fas fa-wallet fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <small class="opacity-75 d-block">Current Monthly Salary</small>
                                                            <h3 class="mb-0 fw-bold" id="currentSalaryDisplay">
                                                                ₹{{ $user && $user->salaries->first() ? number_format($user->salaries->first()->base_salary, 2) : '0.00' }}
                                                            </h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                                    <small class="opacity-75 d-block">Daily Rate</small>
                                                    <h5 class="mb-0" id="currentDailyRateDisplay">
                                                        ₹{{ $user && $user->salaries->first() ? number_format($user->salaries->first()->daily_rate, 2) : '0.00' }}
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-medium">New Base Salary (Monthly) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">₹</span>
                                                <input type="number" class="form-control" name="base_salary" id="baseSalary" 
                                                       step="0.01" min="0" required placeholder="Enter new monthly salary"
                                                       oninput="calculateRatesAndHike()">
                                            </div>
                                            <small class="text-muted">Enter the new monthly salary amount</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-medium">Working Days per Month <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="working_days_per_month" id="workingDays" 
                                                   min="1" max="31" value="26" required oninput="calculateRatesAndHike()">
                                            <small class="text-muted">Standard is 26 days (excluding Sundays)</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Salary Hike Indicator -->
                                    <div id="salaryHikeSection" class="mb-4" style="display: none;">
                                        <div class="card border-0 shadow-sm" id="hikeCard">
                                            <div class="card-body p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <i class="fas fa-chart-line fa-2x mb-2" id="hikeIcon"></i>
                                                            <h4 class="mb-0 fw-bold" id="hikePercentage">0%</h4>
                                                            <small class="text-muted" id="hikeLabel">Change</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Previous Salary:</span>
                                                            <strong id="hikePreviousSalary">₹0</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">New Salary:</span>
                                                            <strong id="hikeNewSalary">₹0</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Difference:</span>
                                                            <strong id="hikeDifference">₹0</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-medium">Daily Rate (Auto-calculated)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">₹</span>
                                                <input type="text" class="form-control bg-light" id="dailyRateDisplay" readonly>
                                            </div>
                                            <small class="text-muted">Base Salary ÷ Working Days</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-medium">Half Day Rate (Auto-calculated)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">₹</span>
                                                <input type="text" class="form-control bg-light" id="halfDayRateDisplay" readonly>
                                            </div>
                                            <small class="text-muted">Daily Rate ÷ 2</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Effective From <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="effective_from" required 
                                               value="{{ old('effective_from', date('Y-m-d')) }}">
                                        <small class="text-muted">The date from which this salary will be applicable. Previous salary will be marked as inactive.</small>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes about this salary change (e.g., Annual increment, Promotion, Performance bonus)...">{{ old('notes') }}</textarea>
                                    </div>
                                    
                                    <div class="alert alert-info border-0 shadow-sm">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle fa-lg"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <strong>Important:</strong> When you update the salary, the new rates will apply from the effective date. 
                                                Salary calculations for days before the effective date will use the previous rates, and days after will use the new rates.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-theme rounded-pill px-5 py-2 shadow-sm">
                                            <i class="fas fa-save me-2"></i> Save Salary
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Salary History -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-history me-2 text-theme"></i>Salary History
                                </h5>
                            </div>
                            <div class="card-body" id="salaryHistoryContainer" style="max-height: 600px; overflow-y: auto;">
                                @if($salaryHistory->count() > 0)
                                    @foreach($salaryHistory as $salary)
                                    <div class="border rounded p-3 mb-3 {{ $salary->is_active ? 'border-success shadow-sm' : 'border-light' }}" 
                                         style="{{ $salary->is_active ? 'background: linear-gradient(135deg, #d1f4e0 0%, #e8f5e9 100%);' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="fw-bold text-success fs-5">₹{{ number_format($salary->base_salary, 2) }}</span>
                                                <span class="text-muted small">/month</span>
                                            </div>
                                            @if($salary->is_active)
                                                <span class="badge bg-success rounded-pill">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill">Inactive</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">
                                            <div class="mb-1">
                                                <i class="fas fa-calendar-plus me-1 text-primary"></i> 
                                                <strong>From:</strong> {{ $salary->effective_from->format('d M Y') }}
                                            </div>
                                            @if($salary->effective_to)
                                                <div class="mb-1">
                                                    <i class="fas fa-calendar-minus me-1 text-danger"></i> 
                                                    <strong>To:</strong> {{ $salary->effective_to->format('d M Y') }}
                                                </div>
                                            @endif
                                            <div class="border-top pt-2 mt-2">
                                                <i class="fas fa-calculator me-1"></i> 
                                                Daily: <strong>₹{{ number_format($salary->daily_rate, 2) }}</strong> | 
                                                Half: <strong>₹{{ number_format($salary->half_day_rate, 2) }}</strong>
                                            </div>
                                        </div>
                                        @if($salary->notes)
                                            <div class="small text-muted mt-2 pt-2 border-top">
                                                <i class="fas fa-sticky-note me-1"></i> {{ Str::limit($salary->notes, 80) }}
                                            </div>
                                        @endif
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-history fa-3x mb-3 opacity-50"></i>
                                        <p class="mb-0">No salary history</p>
                                        <small>Select a staff member to view history</small>
                                    </div>
                                @endif
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

@section('scripts')
<script>
    let currentSalaryAmount = {{ $user && $user->salaries->first() ? $user->salaries->first()->base_salary : 0 }};
    let currentDailyRate = {{ $user && $user->salaries->first() ? $user->salaries->first()->daily_rate : 0 }};
    
    function calculateRatesAndHike() {
        const baseSalary = parseFloat(document.getElementById('baseSalary').value) || 0;
        const workingDays = parseInt(document.getElementById('workingDays').value) || 26;
        
        // Calculate rates
        const dailyRate = baseSalary / workingDays;
        const halfDayRate = dailyRate / 2;
        
        document.getElementById('dailyRateDisplay').value = dailyRate.toFixed(2);
        document.getElementById('halfDayRateDisplay').value = halfDayRate.toFixed(2);
        
        // Calculate and display hike
        if (currentSalaryAmount > 0 && baseSalary > 0) {
            const difference = baseSalary - currentSalaryAmount;
            const percentageChange = (difference / currentSalaryAmount) * 100;
            
            const hikeSection = document.getElementById('salaryHikeSection');
            const hikeCard = document.getElementById('hikeCard');
            const hikeIcon = document.getElementById('hikeIcon');
            const hikePercentage = document.getElementById('hikePercentage');
            const hikeLabel = document.getElementById('hikeLabel');
            
            hikeSection.style.display = 'block';
            
            // Update values
            document.getElementById('hikePreviousSalary').textContent = '₹' + currentSalaryAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('hikeNewSalary').textContent = '₹' + baseSalary.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('hikeDifference').textContent = (difference >= 0 ? '+' : '') + '₹' + Math.abs(difference).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            if (difference > 0) {
                // Positive hike
                hikeCard.style.background = 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)';
                hikeIcon.className = 'fas fa-arrow-trend-up fa-2x mb-2 text-success';
                hikePercentage.className = 'mb-0 fw-bold text-success';
                hikePercentage.textContent = '+' + percentageChange.toFixed(2) + '%';
                hikeLabel.textContent = 'Salary Hike';
            } else if (difference < 0) {
                // Negative change
                hikeCard.style.background = 'linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%)';
                hikeIcon.className = 'fas fa-arrow-trend-down fa-2x mb-2 text-danger';
                hikePercentage.className = 'mb-0 fw-bold text-danger';
                hikePercentage.textContent = percentageChange.toFixed(2) + '%';
                hikeLabel.textContent = 'Salary Decrease';
            } else {
                // No change
                hikeCard.style.background = 'linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%)';
                hikeIcon.className = 'fas fa-equals fa-2x mb-2 text-info';
                hikePercentage.className = 'mb-0 fw-bold text-info';
                hikePercentage.textContent = '0%';
                hikeLabel.textContent = 'No Change';
            }
        } else {
            document.getElementById('salaryHikeSection').style.display = 'none';
        }
    }
    
    function loadUserSalary(userId) {
        if (userId) {
            // Get current salary from the selected option
            const select = document.getElementById('userSelect');
            const selectedOption = select.options[select.selectedIndex];
            const salary = parseFloat(selectedOption.getAttribute('data-current-salary')) || 0;
            
            // Update current salary display
            if (salary > 0) {
                currentSalaryAmount = salary;
                // Estimate daily rate (assuming 26 working days)
                currentDailyRate = salary / 26;
                
                document.getElementById('currentSalaryDisplay').textContent = '₹' + salary.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('currentDailyRateDisplay').textContent = '₹' + currentDailyRate.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('currentSalarySection').style.display = 'block';
            } else {
                currentSalaryAmount = 0;
                currentDailyRate = 0;
                document.getElementById('currentSalarySection').style.display = 'none';
            }
            
            // Reload page to get full salary history
            window.location.href = `{{ route('vendor.salary.create') }}?user_id=${userId}`;
        } else {
            currentSalaryAmount = 0;
            currentDailyRate = 0;
            document.getElementById('currentSalarySection').style.display = 'none';
            document.getElementById('salaryHikeSection').style.display = 'none';
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateRatesAndHike();
        
        // If user is selected, show current salary
        const userSelect = document.getElementById('userSelect');
        if (userSelect.value) {
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            const salary = parseFloat(selectedOption.getAttribute('data-current-salary')) || 0;
            if (salary > 0) {
                document.getElementById('currentSalarySection').style.display = 'block';
            }
        }
    });
</script>
@endsection
