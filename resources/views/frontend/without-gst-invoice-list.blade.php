
@extends('frontend.layouts.app')

@section('title', 'My Without GST Invoices - ' . setting('site_title', 'Frontend App'))

@push('styles')
<style>
    /* Page Header Styles - Gray Theme for Without GST */
    .invoice-page-header {
        background: linear-gradient(135deg, #6c757d, #495057);
        padding: 3rem 0;
        margin-bottom: 0;
        position: relative;
        overflow: hidden;
    }
    
    .invoice-page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    
    .invoice-page-header h1 {
        color: white;
        font-weight: 700;
        margin: 0;
        position: relative;
        z-index: 1;
    }
    
    .invoice-page-header .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .invoice-page-header .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }
    
    .invoice-page-header .breadcrumb-item a:hover {
        color: white;
    }
    
    .invoice-page-header .breadcrumb-item.active {
        color: rgba(255, 255, 255, 0.9);
    }
    
    .invoice-page-header .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.6);
    }
    
    /* Invoice Content Wrapper */
    .invoice-content-wrapper {
        background: #f8f9fa;
        padding: 2rem 0 4rem;
        min-height: 50vh;
    }
    
    /* Tab Navigation Styles */
    .invoice-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    .invoice-tab-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .invoice-tab-btn.active {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: white;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }
    
    .invoice-tab-btn:not(.active) {
        background: white;
        color: var(--text-color);
        border-color: rgba(0, 0, 0, 0.1);
    }
    
    .invoice-tab-btn:not(.active):hover {
        border-color: #6c757d;
        color: #6c757d;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .invoice-tab-btn .badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
        font-size: 0.75rem;
    }
    
    .invoice-tab-btn:not(.active) .badge {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    /* Invoice Card */
    .invoice-main-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .invoice-main-card .card-header {
        background: white;
        border-bottom: 1px solid rgba(108, 117, 125, 0.1);
        padding: 1.5rem 2rem;
    }
    
    .invoice-main-card .card-body {
        padding: 1.5rem 2rem;
    }
    
    /* Table Styles */
    .invoice-table {
        margin-bottom: 0;
    }
    
    .invoice-table thead {
        background: linear-gradient(135deg, #6c757d, #495057);
    }
    
    .invoice-table thead th {
        color: white;
        font-weight: 600;
        border: none;
        padding: 1rem;
        white-space: nowrap;
    }
    
    .invoice-table tbody tr {
    }
    
    .invoice-table tbody tr:hover {
        background: rgba(108, 117, 125, 0.05);
    }
    
    .invoice-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-color: rgba(0, 0, 0, 0.05);
    }
    
    /* Invoice Number Cell */
    .invoice-number {
        font-weight: 600;
        color: #6c757d;
    }
    
    /* Amount Cell */
    .invoice-amount {
        font-weight: 700;
        color: var(--text-color);
    }
    
    /* GST Badge */
    .gst-badge {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
    
    /* Status Badge Styles */
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .status-badge i {
        font-size: 0.75rem;
    }
    
    .status-draft {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }
    
    .status-approved {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }
    
    .status-dispatch {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
    }
    
    .status-delivery {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }
    
    .status-delivered {
        background: linear-gradient(135deg, #155724 0%, #28a745 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }
    
    .status-return {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }
    
    .status-default {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }
    
    /* Modal Styles */
    .modal.fade .modal-dialog {
    }
    
    .modal.show .modal-dialog {
    }
    
    .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: white;
        border: none;
        padding: 1.25rem 1.5rem;
    }
    
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }
    
    .modal-header .btn-close:hover {
        opacity: 1;
    }
    
    .modal-body {
        padding: 2rem;
    }
    
    .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1rem 1.5rem;
    }
    
    /* Loading Spinner */
    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 3px solid rgba(108, 117, 125, 0.1);
        border-top-color: #6c757d;
        border-radius: 50%;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .empty-state-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #6c757d, #495057);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }
    
    .empty-state-icon i {
        font-size: 2.5rem;
        color: white;
    }
    
    .empty-state h5 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 0.75rem;
    }
    
    .empty-state p {
        color: var(--text-muted-color);
        margin-bottom: 1.5rem;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .invoice-page-header {
            padding: 2rem 0;
        }
        
        .invoice-page-header h1 {
            font-size: 1.5rem;
        }
        
        .invoice-main-card .card-header,
        .invoice-main-card .card-body {
            padding: 1rem;
        }
        
        .invoice-table tbody tr:hover {
        }
    }
    
    /* ===== Base Button Styles ===== */
    .btn-theme {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        border: none;
        color: white !important;
        position: relative;
        overflow: hidden;
    }
    
    .btn-theme::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    }
    
    .btn-theme:hover {
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.35);
    }
    
    .btn-theme:hover::before {
        left: 100%;
    }
    
    .btn-outline-theme {
        border: 2px solid #6c757d;
        color: #6c757d;
        background: transparent;
    }
    
    .btn-outline-theme:hover {
        background: #6c757d;
        color: white;
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
    }
    
    /* ===== Action Button Styles ===== */
    .btn-view-theme {
        background: #6c757d;
        border: none;
        color: white !important;
        border-radius: 6px;
        padding: 0.4rem 0.75rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex !important;
        align-items: center;
        gap: 0.25rem;
    }
    
    .btn-view-theme:hover {
        background: #5a6268;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }
    
    .btn-view-theme:hover i,
    .btn-view-theme:hover span {
        color: white !important;
    }
    
    .btn-view-theme.active {
        background: #5a6268;
        color: white !important;
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }
    
    .btn-view-theme.active i,
    .btn-view-theme.active span {
        color: white !important;
    }
    
    .btn-view-theme:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(108, 117, 125, 0.25);
        color: white !important;
    }
    
    .btn-pdf-theme {
        background: linear-gradient(135deg, #b71c1c 0%, #8b0000 100%);
        border: none;
        color: white !important;
        transition: all 0.3s ease;
        display: inline-flex !important;
        align-items: center;
        gap: 0.25rem;
    }
    
    .btn-pdf-theme:hover {
        box-shadow: 0 8px 20px rgba(183, 28, 28, 0.4);
        color: white !important;
        transform: translateY(-2px);
    }
    
    .btn-pdf-theme:hover i,
    .btn-pdf-theme:hover span {
        color: white !important;
    }
    
    .btn-pdf-theme:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(183, 28, 28, 0.25);
        color: white !important;
    }
    
    /* ===== Action Buttons Container ===== */
    .action-buttons {
        white-space: nowrap;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .action-buttons .btn {
        border-radius: 8px;
        font-size: 0.85rem;
        padding: 6px 12px;
    }
    
    /* ===== Empty State ===== */
    .empty-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto;
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.1) 0%, rgba(73, 80, 87, 0.1) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .empty-icon i {
        color: #6c757d;
        opacity: 0.7;
    }
    
    /* ===== Modal Styles ===== */
    .invoice-modal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    }
    
    .invoice-modal .modal-header {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
        border: none;
        padding: 1.25rem 1.5rem;
    }
    
    .invoice-modal .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .btn-close-custom {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
    }
    
    .btn-close-custom:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .invoice-modal .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .invoice-modal .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        padding: 1rem 1.5rem;
        background: #f8f9fa;
    }
    
    .btn-modal-close {
        background: #6c757d;
        border: none;
    }
    
    .btn-modal-close:hover {
        background: #5a6268;
    }
    
    .btn-modal-download {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        position: relative;
        overflow: hidden;
    }
    
    /* Loading Spinner */
    .loading-spinner .spinner-border {
        width: 3rem;
        height: 3rem;
        color: #6c757d;
    }
    
    /* ===== Print Styles ===== */
    @media print {
        .modal-content {
            box-shadow: none !important;
            border: none !important;
        }
        
        .btn {
            display: none !important;
        }
        
        .breadcrumb {
            display: none !important;
        }
    }
    
    /* ===== Responsive Adjustments ===== */
    @media (max-width: 768px) {
        .action-buttons {
            white-space: normal;
            justify-content: flex-start;
        }
        
        .action-buttons .btn {
            font-size: 0.75rem;
            padding: 5px 10px;
        }
        
        .status-badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }
        
        .invoice-modal .modal-content {
            border-radius: 12px;
        }
        
        .invoice-modal .modal-header {
            padding: 1rem;
        }
    }
    
    /* ===== Product Link Styles ===== */
    .product-link {
        color: #6c757d;
        font-weight: 600;
        position: relative;
    }
    
    .product-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: #6c757d;
    }
    
    .product-link:hover {
        color: #495057;
    }
    
    .product-link:hover::after {
        width: 100%;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="invoice-page-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('frontend.home') }}">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">My Without GST Invoices</li>
            </ol>
        </nav>
        <h1>
            <i class="fas fa-file-alt me-2"></i> My Without GST Invoices
        </h1>
    </div>
</div>

<!-- Invoice Content -->
<div class="invoice-content-wrapper">
    <div class="container-fluid">
        <!-- Tab Navigation -->
        <div class="invoice-tabs">
            <a href="{{ route('frontend.cart.proforma.invoices') }}" class="invoice-tab-btn">
                <i class="fas fa-file-invoice"></i>
                With GST Invoices
            </a>
            <a href="{{ route('frontend.cart.without-gst.invoices') }}" class="invoice-tab-btn active">
                <i class="fas fa-file-alt"></i>
                Without GST Invoices
                @if($withoutGstInvoices->count() > 0)
                    <span class="badge">{{ $withoutGstInvoices->count() }}</span>
                @endif
            </a>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="invoice-main-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0" style="color: var(--text-color);">
                                <i class="fas fa-list me-2" style="color: #6c757d;"></i>
                                Without GST Invoice List
                            </h5>
                            @if($withoutGstInvoices->count() > 0)
                            <span class="badge" style="background: #6c757d; padding: 0.5rem 1rem; border-radius: 50px;">
                                {{ $withoutGstInvoices->count() }} {{ Str::plural('Invoice', $withoutGstInvoices->count()) }}
                            </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @if($withoutGstInvoices->count() > 0)
                            <div class="table-responsive">
                                <table class="table invoice-table">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-hashtag me-1"></i> #</th>
                                            <th><i class="fas fa-file-alt me-1"></i> Invoice #</th>
                                            <th><i class="fas fa-calendar me-1"></i> Date</th>
                                            <th><i class="fas fa-rupee-sign me-1"></i> Amount</th>
                                            <th><i class="fas fa-info-circle me-1"></i> Status</th>
                                            <th><i class="fas fa-cog me-1"></i> Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($withoutGstInvoices as $invoice)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="invoice-number">
                                                {{ $invoice->invoice_number }}
                                                <span class="gst-badge">NO GST</span>
                                            </td>
                                            <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                            <td class="invoice-amount">₹{{ number_format($invoice->total_amount, 2) }}</td>
                                            <td>
                                                @switch($invoice->status)
                                                    @case('Draft')
                                                        <span class="status-badge status-draft">
                                                            <i class="fas fa-pencil-alt"></i> Draft
                                                        </span>
                                                        @break
                                                    @case('Approved')
                                                        <span class="status-badge status-approved">
                                                            <i class="fas fa-check-circle"></i> Approved
                                                        </span>
                                                    @break
                                                @case('Dispatch')
                                                        <span class="status-badge status-dispatch">
                                                            <i class="fas fa-truck-loading"></i> Dispatch
                                                        </span>
                                                    @break
                                                @case('Out for Delivery')
                                                        <span class="status-badge status-delivery">
                                                            <i class="fas fa-shipping-fast"></i> Out for Delivery
                                                        </span>
                                                    @break
                                                @case('Delivered')
                                                        <span class="status-badge status-delivered">
                                                            <i class="fas fa-check-double"></i> Delivered
                                                        </span>
                                                    @break
                                                @case('Return')
                                                        <span class="status-badge status-return">
                                                            <i class="fas fa-undo-alt"></i> Return
                                                        </span>
                                                    @break
                                                @default
                                                        <span class="status-badge status-default">
                                                            <i class="fas fa-question-circle"></i> {{ $invoice->status }}
                                                        </span>
                                            @endswitch
                                        </td>
                                        <td class="action-buttons">
                                            <!-- View Button -->
                                            <button class="btn btn-sm btn-view-theme view-invoice" data-invoice-id="{{ $invoice->id }}">
                                                <i class="fas fa-eye me-1"></i>View
                                            </button>
                                            
                                            @if($invoice->status !== 'Draft')
                                                <!-- PDF Download Button -->
                                                <a href="{{ route('frontend.cart.without-gst.invoice.download-pdf', $invoice->id) }}" class="btn btn-sm btn-pdf-theme">
                                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state text-center py-5">
                            <div class="empty-icon mb-3">
                                <i class="fas fa-file-alt fa-3x"></i>
                            </div>
                            <h5 class="mb-2">No without GST invoices found</h5>
                            <p class="mb-0 text-muted">You don't have any without GST invoices yet.</p>
                            <a href="{{ route('frontend.cart.proforma.invoices') }}" class="btn btn-theme mt-3">
                                <i class="fas fa-file-invoice me-2"></i>View With GST Invoices
                                <i class="fas fa-arrow-right ms-2 btn-arrow"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Details Modal -->
<div class="modal fade invoice-modal" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">
                    <i class="fas fa-file-alt me-2"></i>Without GST Invoice Details
                </h5>
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="invoiceModalBody">
                <!-- Invoice details will be loaded here -->
                <div class="text-center py-5">
                    <div class="loading-spinner">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading invoice details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-theme btn-modal-download d-none" id="downloadPdfBtn">
                    <i class="fas fa-file-pdf me-2"></i>Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View invoice button click handler
    document.querySelectorAll('.view-invoice').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.view-invoice').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            const invoiceId = this.getAttribute('data-invoice-id');
            loadInvoiceDetails(invoiceId);
        });
    });
    
    // Download PDF button handler
    document.getElementById('downloadPdfBtn').addEventListener('click', function() {
        const activeButton = document.querySelector('.view-invoice.active');
        if (activeButton) {
            const invoiceId = activeButton.getAttribute('data-invoice-id');
            if (invoiceId) {
                window.location.href = `/cart/without-gst-invoice/${invoiceId}/download-pdf`;
            }
        }
    });
    
    // Load invoice details via AJAX
    function loadInvoiceDetails(invoiceId) {
        const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
        
        // Reset modal body to show loading spinner
        document.getElementById('invoiceModalBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border" style="color: #6c757d;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading invoice details...</p>
            </div>
        `;
        
        modal.show();
        
        // Fetch invoice details
        fetch(`/cart/without-gst-invoice/${invoiceId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Invoice data received:', data);
                
                if (data.error) {
                    document.getElementById('invoiceModalBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>${data.error}
                        </div>
                    `;
                    return;
                }
                
                if (!data.invoice || !data.data) {
                    document.getElementById('invoiceModalBody').innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>Invalid invoice data received.
                        </div>
                    `;
                    return;
                }
                
                renderInvoiceDetails(data);
            })
            .catch(error => {
                console.error('Error loading invoice:', error);
                document.getElementById('invoiceModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Failed to load invoice details. Please try again.
                        <br><small class="text-muted">Error: ${error.message}</small>
                    </div>
                `;
            });
    }
    
    // Get status badge HTML based on status
    function getStatusBadge(status) {
        switch(status) {
            case 'Draft':
                return '<span class="badge bg-secondary">Draft</span>';
            case 'Approved':
                return '<span class="badge bg-success">Approved</span>';
            case 'Dispatch':
                return '<span class="badge bg-info">Dispatch</span>';
            case 'Out for Delivery':
                return '<span class="badge bg-primary">Out for Delivery</span>';
            case 'Delivered':
                return '<span class="badge bg-success">Delivered</span>';
            case 'Return':
                return '<span class="badge bg-danger">Return</span>';
            default:
                return '<span class="badge bg-secondary">' + status + '</span>';
        }
    }
    
    // Render invoice details in the modal
    function renderInvoiceDetails(data) {
        const invoice = data.invoice;
        const invoiceData = data.data || {};
        const storeData = data.store || null;
        
        console.log('Rendering invoice:', invoice);
        console.log('Invoice data:', invoiceData);
        console.log('Store data:', storeData);
        
        const siteTitle = document.querySelector('meta[name="site-title"]')?.getAttribute('content') || '{{ setting("site_title", "Frontend App") }}';
        const companyAddress = document.querySelector('meta[name="company-address"]')?.getAttribute('content') || '{{ setting("company_address", "Company Address") }}';
        const companyEmail = document.querySelector('meta[name="company-email"]')?.getAttribute('content') || '{{ setting("company_email", "company@example.com") }}';
        const companyPhone = document.querySelector('meta[name="company-phone"]')?.getAttribute('content') || '{{ setting("company_phone", "+1 (555) 123-4567") }}';
        
        // Build "From" section - use store details if available, otherwise use site settings
        let fromHtml = '';
        if (storeData) {
            fromHtml = `
                ${storeData.store_logo ? `<img src="${storeData.store_logo}" alt="${storeData.store_name}" style="max-height: 50px; max-width: 150px; margin-bottom: 10px; object-fit: contain;">` : ''}
                <p class="mb-1 fw-bold" style="color: #6c757d; font-size: 1.1rem;">${storeData.store_name}</p>
                ${storeData.full_address ? `<p class="mb-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i>${storeData.full_address}</p>` : (storeData.business_address ? `<p class="mb-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i>${storeData.business_address}</p>` : '')}
                ${storeData.business_email ? `<p class="mb-1"><i class="fas fa-envelope me-1 text-muted"></i>${storeData.business_email}</p>` : ''}
                ${storeData.business_phone ? `<p class="mb-1"><i class="fas fa-phone me-1 text-muted"></i>${storeData.business_phone}</p>` : ''}
            `;
        } else {
            fromHtml = `
                <p class="mb-1 fw-bold" style="color: #6c757d; font-size: 1.1rem;">${siteTitle}</p>
                <p class="mb-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i>${companyAddress}</p>
                <p class="mb-1"><i class="fas fa-envelope me-1 text-muted"></i>${companyEmail}</p>
                <p class="mb-1"><i class="fas fa-phone me-1 text-muted"></i>${companyPhone}</p>
            `;
        }
        
        let customerHtml = '';
        if (invoiceData.customer) {
            customerHtml = `
                <p class="mb-1 fw-bold" style="font-size: 1.1rem;">${invoiceData.customer.name || 'N/A'}</p>
                <p class="mb-1"><i class="fas fa-envelope me-1 text-muted"></i>${invoiceData.customer.email || 'N/A'}</p>
                ${invoiceData.customer.address ? `<p class="mb-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i>${invoiceData.customer.address}</p>` : ''}
                ${invoiceData.customer.mobile_number ? `<p class="mb-1"><i class="fas fa-phone me-1 text-muted"></i>${invoiceData.customer.mobile_number}</p>` : ''}
            `;
        } else {
            customerHtml = `
                <p class="mb-1 fw-bold">Guest Customer</p>
                <p class="mb-1 text-muted">N/A</p>
            `;
        }
        
        let cartItemsHtml = '';
        const cartItems = invoiceData.cart_items || invoiceData.items || invoiceData.products || [];
        
        console.log('Cart items:', cartItems);
        
        if (cartItems && cartItems.length > 0) {
            let index = 1;
            cartItems.forEach(item => {
                const price = parseFloat(item.price) || parseFloat(item.unit_price) || 0;
                const total = parseFloat(item.total) || parseFloat(item.line_total) || (price * (parseInt(item.quantity) || 0));
                const quantity = parseInt(item.quantity) || parseInt(item.qty) || 0;
                const productName = item.product_name || item.name || item.title || 'Product';
                const productSlug = item.product_slug || item.slug || '';
                const productDesc = item.product_description || item.description || '';
                
                const productLink = productSlug ? `/product/${productSlug}` : '#';
                const productNameHtml = productSlug 
                    ? `<a href="${productLink}" class="product-link text-decoration-none">${productName}</a>`
                    : productName;
                
                // Build variation attributes display
                let variationHtml = '';
                console.log('Item variation check:', {
                    product_name: item.product_name,
                    has_variation_id: !!item.product_variation_id,
                    variation_id: item.product_variation_id,
                    has_attributes: !!item.variation_attributes,
                    attributes: item.variation_attributes,
                    sku: item.variation_sku
                });
                
                if (item.product_variation_id && item.variation_attributes) {
                    const attributes = item.variation_attributes;
                    const attributePairs = [];
                    
                    // Handle both object and array formats
                    if (typeof attributes === 'object' && !Array.isArray(attributes)) {
                        for (const [key, value] of Object.entries(attributes)) {
                            attributePairs.push(`<strong>${key}:</strong> ${value}`);
                        }
                    }
                    
                    console.log('Attribute pairs built:', attributePairs);
                    
                    if (attributePairs.length > 0) {
                        variationHtml = `<br><small class="text-muted" style="font-size: 0.85rem;">${attributePairs.join(', ')}</small>`;
                    }
                }
                
                // Add SKU if available
                if (item.product_variation_id && item.variation_sku) {
                    variationHtml += `<br><small class="text-muted" style="font-size: 0.85rem;"><strong>SKU:</strong> ${item.variation_sku}</small>`;
                }
                
                console.log('Final variation HTML:', variationHtml);
                
                cartItemsHtml += `
                    <tr>
                        <td>${index++}</td>
                        <td>
                            <div>
                                <h6 class="mb-0">${productNameHtml}${variationHtml}</h6>
                                ${productDesc ? `<small class="text-muted">${productDesc.substring(0, 50)}${productDesc.length > 50 ? '...' : ''}</small>` : ''}
                            </div>
                        </td>
                        <td>₹${price.toFixed(2)}</td>
                        <td>${quantity}</td>
                        <td>₹${total.toFixed(2)}</td>
                    </tr>
                `;
            });
        } else {
            cartItemsHtml = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        <i class="fas fa-inbox me-2"></i>No items found in this invoice
                    </td>
                </tr>
            `;
        }
        
        // Ensure all financial values are valid numbers (no GST for this invoice type)
        const subtotal = parseFloat(invoiceData.subtotal) || 0;
        const shipping = parseFloat(invoiceData.shipping) || 0;
        const discountAmount = parseFloat(invoiceData.discount_amount) || 0;
        const invoiceTotal = parseFloat(invoiceData.total) || 0;
        
        // Show/hide download button based on status
        if (invoice.status === 'Draft') {
            document.getElementById('downloadPdfBtn').classList.add('d-none');
        } else {
            document.getElementById('downloadPdfBtn').classList.remove('d-none');
        }
        
        document.getElementById('invoiceModalBody').innerHTML = `
            <div class="container-fluid">
                <div class="alert alert-secondary mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Without GST Invoice</strong> - This invoice does not include GST/Tax charges.
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="fw-bold mb-3"><i class="fas fa-store me-2" style="color: #6c757d;"></i>From:</h5>
                        ${fromHtml}
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="fw-bold mb-3"><i class="fas fa-user me-2" style="color: #6c757d;"></i>To:</h5>
                        ${customerHtml}
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Invoice #:</strong> ${invoice.invoice_number} <span class="badge bg-secondary">NO GST</span></p>
                        <p class="mb-1"><strong>Date:</strong> ${invoiceData.invoice_date || new Date(invoice.created_at).toLocaleDateString()}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Status:</strong> 
                            ${getStatusBadge(invoice.status)}
                        </p>
                        ${storeData ? `<p class="mb-1"><strong>Store:</strong> <a href="/store/${storeData.store_slug}" class="text-decoration-none" style="color: #6c757d;">${storeData.store_name}</a></p>` : ''}
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${cartItemsHtml}
                        </tbody>
                    </table>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Notes</h5>
                                <p class="mb-0">${invoiceData.notes || 'This is a proforma invoice without GST. Payment is due upon receipt.'}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    ${subtotal > 0 ? `<tr>
                                        <td class="fw-bold">Subtotal:</td>
                                        <td class="text-end">₹${subtotal.toFixed(2)}</td>
                                    </tr>` : ''}
                                    ${shipping > 0 ? `<tr>
                                        <td class="fw-bold">Shipping:</td>
                                        <td class="text-end">₹${shipping.toFixed(2)}</td>
                                    </tr>` : ''}
                                    ${discountAmount > 0 ? `<tr>
                                        <td class="fw-bold">Discount Amount:</td>
                                        <td class="text-end">-₹${discountAmount.toFixed(2)}</td>
                                    </tr>` : ''}
                                    <tr class="border-top">
                                        <td class="fw-bold">Total:</td>
                                        <td class="text-end fw-bold">₹${invoiceTotal.toFixed(2)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
});
</script>
@endsection
