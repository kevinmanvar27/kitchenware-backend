@extends('vendor.layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Invoices'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Total Invoices</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $totalInvoices }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-file-invoice fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Total Amount</h6>
                                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($totalAmount, 2) }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-rupee-sign fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Delivered</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $deliveredCount }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Pending</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $pendingCount }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-clock fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form action="{{ route('vendor.invoices.index') }}" method="GET" class="row g-3">
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select rounded-pill">
                                    <option value="">All Status</option>
                                    <option value="Draft" {{ $status == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Approved" {{ $status == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Dispatch" {{ $status == 'Dispatch' ? 'selected' : '' }}>Dispatch</option>
                                    <option value="Out for Delivery" {{ $status == 'Out for Delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                    <option value="Delivered" {{ $status == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="Return" {{ $status == 'Return' ? 'selected' : '' }}>Return</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">Payment</label>
                                <select name="payment_status" class="form-select rounded-pill">
                                    <option value="">All</option>
                                    <option value="unpaid" {{ $paymentStatus == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    <option value="partial" {{ $paymentStatus == 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="paid" {{ $paymentStatus == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">Client</label>
                                <select name="client_id" class="form-select rounded-pill">
                                    <option value="">All Clients</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">From Date</label>
                                <input type="date" name="date_from" class="form-control rounded-pill" value="{{ $dateFrom }}">
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">To Date</label>
                                <input type="date" name="date_to" class="form-control rounded-pill" value="{{ $dateTo }}">
                            </div>
                            <div class="col-12 col-sm-12 col-md-2 d-flex align-items-end gap-2 flex-wrap">
                                <button type="submit" class="btn btn-theme rounded-pill px-4 flex-grow-1 flex-md-grow-0">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('vendor.invoices.index') }}" class="btn btn-outline-secondary rounded-pill px-4 flex-grow-1 flex-md-grow-0">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Invoices Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0 fw-bold">All Invoices</h4>
                            <p class="mb-0 text-muted small">Manage your invoices</p>
                        </div>
                        <a href="{{ route('vendor.invoices.create') }}" class="btn btn-theme rounded-pill">
                            <i class="fas fa-plus me-1"></i> Create Invoice
                        </a>
                    </div>
                    
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if($invoices->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Invoice #</th>
                                            <th class="d-none d-md-table-cell">Customer</th>
                                            <th class="d-none d-lg-table-cell">Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th class="d-none d-sm-table-cell">Payment</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoices as $index => $invoice)
                                        <tr>
                                            <td>{{ $invoices->firstItem() + $index }}</td>
                                            <td>
                                                <a href="{{ route('vendor.invoices.show', $invoice) }}" class="text-decoration-none fw-medium">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                @php
                                                    $invoiceData = $invoice->invoice_data;
                                                    if (is_string($invoiceData)) {
                                                        $invoiceData = json_decode($invoiceData, true);
                                                    }
                                                    $customerName = $invoiceData['customer']['name'] ?? null;
                                                @endphp
                                                @if($customerName)
                                                    {{ $customerName }}
                                                @elseif($invoice->user)
                                                    {{ $invoice->user->name }}
                                                @else
                                                    <span class="text-muted">Guest</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-lg-table-cell">{{ $invoice->created_at->format('d M Y') }}</td>
                                            <td class="fw-bold">₹{{ number_format($invoice->total_amount, 2) }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'Draft' => 'secondary',
                                                        'Approved' => 'info',
                                                        'Dispatch' => 'primary',
                                                        'Out for Delivery' => 'warning',
                                                        'Delivered' => 'success',
                                                        'Return' => 'danger',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                                    {{ $invoice->status }}
                                                </span>
                                            </td>
                                            <td class="d-none d-sm-table-cell">
                                                @switch($invoice->payment_status)
                                                    @case('unpaid')
                                                        <span class="badge bg-secondary">Unpaid</span>
                                                        @break
                                                    @case('partial')
                                                        <span class="badge bg-warning">Partial</span>
                                                        @break
                                                    @case('paid')
                                                        <span class="badge bg-success">Paid</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('vendor.invoices.show', $invoice) }}" 
                                                       class="btn btn-outline-primary rounded-start-pill px-2 px-md-3">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <!-- <a href="{{ route('vendor.invoices.print', $invoice) }}" 
                                                       class="btn btn-outline-secondary px-2 px-md-3" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a> -->
                                                    <a href="{{ route('vendor.invoices.download', $invoice) }}" 
                                                       class="btn btn-outline-success rounded-end-pill px-2 px-md-3">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-center mt-4">
                                {{ $invoices->withQueryString()->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <h5 class="mb-2">No invoices found</h5>
                                <p class="mb-3 text-muted">Create your first invoice to get started.</p>
                                <a href="{{ route('vendor.invoices.create') }}" class="btn btn-theme rounded-pill">
                                    <i class="fas fa-plus me-1"></i> Create Invoice
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection