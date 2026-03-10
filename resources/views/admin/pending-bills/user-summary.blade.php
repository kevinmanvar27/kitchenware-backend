@extends('admin.layouts.app')

@section('title', 'User Pending Summary - ' . setting('site_title', 'Admin Panel'))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'User Pending Summary'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ route('admin.pending-bills.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-1"></i> Back to All Bills
                    </a>
                </div>

                <!-- Overall Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Overall Total</h6>
                                        <h3 class="mb-0 fw-bold">₹{{ number_format($overallTotal, 2) }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                        <i class="fas fa-rupee-sign fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Overall Paid</h6>
                                        <h3 class="mb-0 fw-bold">₹{{ number_format($overallPaid, 2) }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 opacity-75">Overall Pending</h6>
                                        <h3 class="mb-0 fw-bold">₹{{ number_format($overallPending, 2) }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                        <i class="fas fa-clock fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Summary Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h4 class="card-title mb-0 fw-bold">Users with Pending Bills</h4>
                        <p class="mb-0 text-muted">Summary of pending amounts by user</p>
                    </div>
                    
                    <div class="card-body">
                        @if($userSummary->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover" id="userSummaryTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Customer Name</th>
                                            <th>Mobile</th>
                                            <th>Total Bills</th>
                                            <th>Total Amount</th>
                                            <th>Paid Amount</th>
                                            <th>Pending Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($userSummary as $index => $summary)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($summary->user)
                                                    @php
                                                        $displayName = $summary->user->name;
                                                        $displayAvatar = $summary->user->avatar;
                                                        // If user is deleted, try to get original info from invoice_data
                                                        if ($displayName === 'Deleted User') {
                                                            $userInvoice = \App\Models\ProformaInvoice::where('user_id', $summary->user_id)->first();
                                                            if ($userInvoice) {
                                                                $invoiceData = is_array($userInvoice->invoice_data) ? $userInvoice->invoice_data : json_decode($userInvoice->invoice_data, true);
                                                                if (isset($invoiceData['customer']['name'])) {
                                                                    $displayName = $invoiceData['customer']['name'];
                                                                }
                                                                if (isset($invoiceData['customer']['avatar'])) {
                                                                    $displayAvatar = $invoiceData['customer']['avatar'];
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="d-flex align-items-center">
                                                        @if($displayAvatar)
                                                            <img src="{{ asset('storage/' . $displayAvatar) }}" 
                                                                 class="rounded-circle me-2" width="32" height="32">
                                                        @else
                                                            <div class="bg-theme rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                                 style="width: 32px; height: 32px;">
                                                                <span class="text-white small">{{ substr($displayName, 0, 1) }}</span>
                                                            </div>
                                                        @endif
                                                        {{ $displayName }}
                                                        @if($summary->user->name === 'Deleted User')
                                                            <span class="badge bg-secondary ms-2" style="font-size: 0.65rem;">Deleted</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    Guest
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $displayMobile = $summary->user->mobile_number ?? null;
                                                    // If user is deleted, try to get original mobile from invoice_data
                                                    if ($summary->user && $summary->user->name === 'Deleted User') {
                                                        $userInvoice = \App\Models\ProformaInvoice::where('user_id', $summary->user_id)->first();
                                                        if ($userInvoice) {
                                                            $invoiceData = is_array($userInvoice->invoice_data) ? $userInvoice->invoice_data : json_decode($userInvoice->invoice_data, true);
                                                            if (isset($invoiceData['customer']['mobile_number'])) {
                                                                $displayMobile = $invoiceData['customer']['mobile_number'];
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                {{ $displayMobile ?? '-' }}
                                            </td>
                                            <td><span class="badge bg-primary">{{ $summary->total_bills }}</span></td>
                                            <td>₹{{ number_format($summary->total_amount, 2) }}</td>
                                            <td class="text-success">₹{{ number_format($summary->total_paid, 2) }}</td>
                                            <td class="text-danger fw-bold">₹{{ number_format($summary->total_pending, 2) }}</td>
                                            <td>
                                                @if($summary->user_id)
                                                    <a href="{{ route('admin.pending-bills.user', $summary->user_id) }}" 
                                                       class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                        <i class="fas fa-eye me-1"></i> View Bills
                                                    </a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5 class="mb-2">No pending bills!</h5>
                                <p class="mb-0 text-muted">All bills have been paid.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#userSummaryTable').DataTable({
        "pageLength": 25,
        "ordering": true,
        "info": true,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [7] }
        ],
        "order": [[6, 'desc']], // Sort by pending amount descending
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries"
        }
    });
    $('.dataTables_length select').css('width', '80px');
});
</script>
@endsection
