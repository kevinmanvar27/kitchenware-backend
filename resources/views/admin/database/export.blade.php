@extends('admin.layouts.app')

@section('title', 'Database Export')

@push('styles')
<style>
    /* ===== GENERAL STYLES ===== */
    :root {
        --primary-color: #FF6B00;
        --primary-dark: #e65100;
        --primary-light: #ff8534;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --info-color: #3b82f6;
        --text-dark: #1f2937;
        --text-muted: #6b7280;
        --border-color: #e5e7eb;
        --bg-light: #f9fafb;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    /* ===== PAGE HEADER ===== */
    .page-header-section {
        margin-bottom: 2rem;
    }

    .header-icon-wrapper {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 8px 16px rgba(255, 107, 0, 0.3);
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .page-subtitle {
        color: var(--text-muted);
        font-size: 1rem;
    }

    .vendor-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: var(--primary-color);
        color: white;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-left: 8px;
    }

    /* ===== MODERN CARD ===== */
    .modern-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .modern-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .card-icon-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px;
        border-bottom: 1px solid var(--border-color);
    }

    .icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
    }

    .icon-wrapper.orange {
        background: linear-gradient(135deg, #FF6B00, #e65100);
    }

    .icon-wrapper.green {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .icon-wrapper.blue {
        background: linear-gradient(135deg, #FF6B00, #e65100);
    }

    .header-content .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    .header-content .card-subtitle {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin: 0;
    }

    .card-content {
        padding: 24px;
    }

    /* ===== MODERN SELECT ===== */
    .modern-select {
        width: 100%;
        padding: 14px 18px;
        font-size: 1rem;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        background: white;
        color: var(--text-dark);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .modern-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
    }

    /* ===== INFO BANNER ===== */
    .info-banner {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 20px;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border-radius: 12px;
        border-left: 4px solid var(--info-color);
    }

    .banner-icon {
        width: 40px;
        height: 40px;
        background: var(--info-color);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .banner-content strong {
        display: block;
        color: var(--text-dark);
        font-size: 1rem;
        margin-bottom: 4px;
    }

    .banner-content p {
        color: var(--text-muted);
        font-size: 0.875rem;
        margin: 0;
    }

    /* ===== QUICK ACTIONS ===== */
    .quick-actions-card {
        padding: 20px 24px;
    }

    .selection-counter {
        text-align: center;
    }

    .counter-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        line-height: 1;
    }

    .counter-label {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* ===== TABLES CARD ===== */
    .tables-card {
        padding: 0;
    }

    .card-header-section {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-light);
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    /* ===== ACCORDION ===== */
    .tables-accordion {
        padding: 0;
    }

    .accordion-group {
        border-bottom: 1px solid var(--border-color);
    }

    .accordion-group:last-child {
        border-bottom: none;
    }

    .accordion-header-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .accordion-header-custom:hover {
        background: var(--bg-light);
    }

    .accordion-header-custom.active {
        background: var(--bg-light);
        border-bottom: 1px solid var(--border-color);
    }

    .accordion-left {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
    }

    .group-checkbox-wrapper {
        flex-shrink: 0;
    }

    .group-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .group-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
    }

    .group-details {
        flex: 1;
    }

    .group-name {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
        margin-bottom: 2px;
    }

    .group-count {
        font-size: 0.875rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .accordion-arrow {
        color: var(--text-muted);
        transition: transform 0.3s ease;
        font-size: 0.875rem;
    }

    .accordion-header-custom.active .accordion-arrow {
        transform: rotate(180deg);
    }

    .accordion-content {
        padding: 24px;
        background: var(--bg-light);
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ===== TABLES GRID ===== */
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 12px;
    }

    .table-item {
        position: relative;
    }

    .table-item input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .table-label {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: white;
        border: 2px solid var(--border-color);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .table-label:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .table-item input:checked + .table-label {
        background: linear-gradient(135deg, rgba(255, 107, 0, 0.1), rgba(230, 81, 0, 0.05));
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
    }

    .table-icon {
        width: 32px;
        height: 32px;
        background: var(--bg-light);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-size: 0.875rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .table-item input:checked + .table-label .table-icon {
        background: var(--primary-color);
        color: white;
    }

    .table-name {
        flex: 1;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-dark);
    }

    .check-indicator {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .table-item input:checked + .table-label .check-indicator {
        background: var(--success-color);
    }

    /* ===== MODERN CHECKBOX ===== */
    .modern-checkbox {
        width: 22px;
        height: 22px;
        border: 2px solid var(--border-color);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        appearance: none;
        background: white;
        position: relative;
    }

    .modern-checkbox:checked {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .modern-checkbox:checked::after {
        content: '\f00c';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.75rem;
    }

    .modern-checkbox:indeterminate {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .modern-checkbox:indeterminate::after {
        content: '\f068';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.75rem;
    }

    /* ===== EXPORT OPTIONS ===== */
    .option-toggle-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: var(--bg-light);
        border-radius: 12px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .option-toggle-item:last-of-type {
        margin-bottom: 16px;
    }

    .option-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .option-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .option-icon.orange {
        background: linear-gradient(135deg, #FF6B00, #e65100);
    }

    .option-icon.green {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .option-details h6 {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0 0 2px 0;
    }

    .option-details p {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin: 0;
    }

    /* ===== TOGGLE SWITCH ===== */
    .toggle-switch {
        position: relative;
    }

    .toggle-switch input[type="checkbox"] {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }

    .toggle-switch label {
        display: block;
        width: 52px;
        height: 28px;
        background: var(--border-color);
        border-radius: 14px;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
        margin: 0;
    }

    .toggle-switch label::after {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        background: white;
        border-radius: 50%;
        top: 3px;
        left: 3px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .toggle-switch input:checked + label {
        background: var(--success-color);
    }

    .toggle-switch input:checked + label::after {
        left: 27px;
    }

    /* ===== WARNING BOX ===== */
    .warning-box {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px;
        background: rgba(245, 158, 11, 0.1);
        border-left: 3px solid var(--warning-color);
        border-radius: 8px;
        font-size: 0.875rem;
        color: #92400e;
    }

    .warning-box i {
        color: var(--warning-color);
    }

    /* ===== EXPORT BUTTON ===== */
    .export-btn {
        padding: 16px 24px;
        font-size: 1.125rem;
        font-weight: 600;
        border-radius: 12px;
        border: none;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        box-shadow: 0 8px 16px rgba(255, 107, 0, 0.3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .export-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(255, 107, 0, 0.4);
    }

    .export-btn:hover::before {
        left: 100%;
    }

    .export-btn:active {
        transform: translateY(0);
    }

    .export-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    /* ===== TIPS CARD ===== */
    .tips-card {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border: 2px solid #fbbf24;
    }

    .tips-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 20px;
        border-bottom: 1px solid #fbbf24;
        font-weight: 600;
        color: #92400e;
        font-size: 1rem;
    }

    .tips-header i {
        color: #f59e0b;
        font-size: 1.25rem;
    }

    .tips-list {
        list-style: none;
        padding: 16px 20px;
        margin: 0;
    }

    .tips-list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
        font-size: 0.875rem;
        color: #78350f;
    }

    .tips-list li:last-child {
        margin-bottom: 0;
    }

    .tips-list li i {
        color: #10b981;
        margin-top: 2px;
        flex-shrink: 0;
    }

    /* ===== MODERN ALERT ===== */
    .modern-alert {
        border-radius: 12px;
        border: none;
        padding: 16px 20px;
    }

    .alert-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-right: 16px;
        flex-shrink: 0;
    }

    .alert-icon.success {
        background: var(--success-color);
        color: white;
    }

    .alert-icon.danger {
        background: var(--danger-color);
        color: white;
    }

    /* ===== STICKY SIDEBAR ===== */
    .sticky-sidebar {
        position: sticky;
        top: 20px;
    }

    /* ===== BUTTONS ===== */
    .btn {
        border-radius: 10px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-lg {
        padding: 12px 24px;
        font-size: 1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        border: none;
    }

    .btn-outline-primary {
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
    }

    .btn-outline-primary:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-sm {
        padding: 8px 16px;
        font-size: 0.875rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 991px) {
        .sticky-sidebar {
            position: relative;
            top: 0;
        }

        .tables-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
    }

    @media (max-width: 767px) {
        .page-title {
            font-size: 1.5rem;
        }

        .header-icon-wrapper {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }

        .tables-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Database Export'])
            
            <div class="pt-4 pb-5">
                <!-- Page Header -->
                <div class="page-header-section mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <div class="header-icon-wrapper me-3">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div>
                                    <h1 class="page-title mb-1">Database Export</h1>
                                    <p class="page-subtitle mb-0">
                                        Backup and export your database tables
                                        @if(auth()->user()->isVendor())
                                            @php
                                                $currentVendor = auth()->user()->vendor;
                                            @endphp
                                            @if($currentVendor)
                                                <span class="vendor-badge">
                                                    <i class="fas fa-store"></i>{{ $currentVendor->store_name }}
                                                </span>
                                            @endif
                                        @elseif(auth()->user()->isVendorStaff())
                                            @php
                                                $staffRecord = auth()->user()->vendorStaff;
                                                $currentVendor = $staffRecord ? $staffRecord->vendor : null;
                                            @endphp
                                            @if($currentVendor)
                                                <span class="vendor-badge">
                                                    <i class="fas fa-store"></i>{{ $currentVendor->store_name }} (Staff)
                                                </span>
                                            @endif
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="{{ route('admin.database.import.index') }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>Import Database
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show modern-alert" role="alert">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="flex-grow-1">{{ session('success') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon danger">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="flex-grow-1">{{ session('error') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.database.export') }}" method="POST" id="exportForm">
                    @csrf

                    <div class="row g-4">
                        <!-- Left Column - Tables Selection -->
                        <div class="col-lg-8">
                            <!-- Vendor Selection (Super Admin Only) -->
                            @if(auth()->user()->isSuperAdmin() && isset($vendors) && count($vendors) > 0)
                                <div class="modern-card vendor-selection-card mb-4">
                                    <div class="card-icon-header">
                                        <div class="icon-wrapper orange">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div class="header-content">
                                            <h5 class="card-title">Vendor Selection</h5>
                                            <p class="card-subtitle">Choose specific vendor or export all data</p>
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <select name="vendor_id" id="vendor_id" class="modern-select">
                                            <option value="">🌐 All Data (Full Database)</option>
                                            @foreach($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">
                                                    🏪 {{ $vendor->store_name }} ({{ $vendor->user->name }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(auth()->user()->isVendor() || auth()->user()->isVendorStaff())
                                <div class="info-banner mb-4">
                                    <div class="banner-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="banner-content">
                                        <strong>Vendor Mode</strong>
                                        <p>You can only export your own store data.</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Quick Actions -->
                            <div class="modern-card quick-actions-card mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="selection-counter">
                                            <div class="counter-value" id="selectedCount">0</div>
                                            <div class="counter-label">Tables Selected</div>
                                        </div>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-primary" onclick="selectAll()">
                                            <i class="fas fa-check-double me-1"></i>Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                            <i class="fas fa-times me-1"></i>Clear All
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tables Accordion -->
                            <div class="modern-card tables-card">
                                <div class="card-header-section">
                                    <h5 class="section-title">
                                        <i class="fas fa-table me-2"></i>Database Tables
                                    </h5>
                                </div>
                                <div class="tables-accordion">
                                    @foreach($groupedTables as $groupName => $tables)
                                        <div class="accordion-group">
                                            <div class="accordion-header-custom" onclick="toggleAccordion('{{ Str::slug($groupName) }}')">
                                                <div class="accordion-left">
                                                    <div class="group-checkbox-wrapper" onclick="event.stopPropagation()">
                                                        <input type="checkbox" 
                                                               class="modern-checkbox group-checkbox" 
                                                               id="group_{{ Str::slug($groupName) }}" 
                                                               onchange="toggleGroup('{{ Str::slug($groupName) }}')">
                                                    </div>
                                                    <div class="group-info">
                                                        <div class="group-icon">
                                                            <i class="fas fa-folder"></i>
                                                        </div>
                                                        <div class="group-details">
                                                            <h6 class="group-name">{{ $groupName }}</h6>
                                                            <span class="group-count">{{ count($tables) }} tables</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion-arrow">
                                                    <i class="fas fa-chevron-down"></i>
                                                </div>
                                            </div>
                                            <div class="accordion-content" id="content_{{ Str::slug($groupName) }}" style="display: none;">
                                                <div class="tables-grid">
                                                    @foreach($tables as $table)
                                                        <div class="table-item">
                                                            <input type="checkbox" 
                                                                   class="modern-checkbox table-checkbox group-{{ Str::slug($groupName) }}" 
                                                                   name="tables[]" 
                                                                   value="{{ $table }}" 
                                                                   id="table_{{ $table }}">
                                                            <label for="table_{{ $table }}" class="table-label">
                                                                <div class="table-icon">
                                                                    <i class="fas fa-table"></i>
                                                                </div>
                                                                <span class="table-name">{{ $table }}</span>
                                                                <div class="check-indicator">
                                                                    <i class="fas fa-check"></i>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @error('tables')
                                <div class="alert alert-danger modern-alert mt-3">
                                    <div class="alert-icon danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div>{{ $message }}</div>
                                </div>
                            @enderror
                        </div>

                        <!-- Right Column - Export Options -->
                        <div class="col-lg-4">
                            <div class="sticky-sidebar">
                                <!-- Export Options Card -->
                                <div class="modern-card export-options-card mb-4">
                                    <div class="card-icon-header">
                                        <div class="icon-wrapper green">
                                            <i class="fas fa-sliders-h"></i>
                                        </div>
                                        <div class="header-content">
                                            <h5 class="card-title">Export Options</h5>
                                            <p class="card-subtitle">Configure what to export</p>
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <div class="option-toggle-item">
                                            <div class="option-info">
                                                <div class="option-icon orange">
                                                    <i class="fas fa-sitemap"></i>
                                                </div>
                                                <div class="option-details">
                                                    <h6>Table Structure</h6>
                                                    <p>Include CREATE TABLE statements</p>
                                                </div>
                                            </div>
                                            <div class="toggle-switch">
                                                <input type="checkbox" name="include_structure" id="include_structure" value="1" checked>
                                                <label for="include_structure"></label>
                                            </div>
                                        </div>

                                        <div class="option-toggle-item">
                                            <div class="option-info">
                                                <div class="option-icon green">
                                                    <i class="fas fa-database"></i>
                                                </div>
                                                <div class="option-details">
                                                    <h6>Table Data</h6>
                                                    <p>Include INSERT statements with data</p>
                                                </div>
                                            </div>
                                            <div class="toggle-switch">
                                                <input type="checkbox" name="include_data" id="include_data" value="1" checked>
                                                <label for="include_data"></label>
                                            </div>
                                        </div>

                                        <div class="warning-box">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <span>At least one option must be selected</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Export Button -->
                                <button type="submit" class="btn btn-primary btn-lg w-100 export-btn" id="exportBtn">
                                    <i class="fas fa-download me-2"></i>
                                    <span>Export Database</span>
                                </button>

                                <!-- Tips Card -->
                                <div class="modern-card tips-card mt-4">
                                    <div class="tips-header">
                                        <i class="fas fa-lightbulb"></i>
                                        <span>Quick Tips</span>
                                    </div>
                                    <ul class="tips-list">
                                        <li>
                                            <i class="fas fa-check-circle"></i>
                                            <span>Click on group names to expand/collapse tables</span>
                                        </li>
                                        <li>
                                            <i class="fas fa-check-circle"></i>
                                            <span>Use group checkboxes to select all tables in a category</span>
                                        </li>
                                        <li>
                                            <i class="fas fa-check-circle"></i>
                                            <span>Export files are saved in SQL format</span>
                                        </li>
                                        <li>
                                            <i class="fas fa-check-circle"></i>
                                            <span>Include both structure and data for full backup</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle accordion
function toggleAccordion(groupSlug) {
    const header = event.currentTarget;
    const content = document.getElementById('content_' + groupSlug);
    
    // Toggle active class
    header.classList.toggle('active');
    
    // Toggle content visibility
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
    } else {
        content.style.display = 'none';
    }
}

// Select all tables
function selectAll() {
    document.querySelectorAll('.table-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    document.querySelectorAll('.group-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
}

// Deselect all tables
function deselectAll() {
    document.querySelectorAll('.table-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.querySelectorAll('.group-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        checkbox.indeterminate = false;
    });
    updateSelectedCount();
}

// Toggle group selection
function toggleGroup(groupSlug) {
    const groupCheckbox = document.getElementById('group_' + groupSlug);
    const tableCheckboxes = document.querySelectorAll('.group-' + groupSlug);
    
    tableCheckboxes.forEach(checkbox => {
        checkbox.checked = groupCheckbox.checked;
    });
    
    updateSelectedCount();
}

// Update group checkbox when individual table is checked/unchecked
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.table-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateGroupCheckbox(this);
            updateSelectedCount();
        });
    });
    
    // Initial count
    updateSelectedCount();
});

function updateGroupCheckbox(tableCheckbox) {
    const groupClass = Array.from(tableCheckbox.classList).find(cls => cls.startsWith('group-'));
    if (groupClass) {
        const groupSlug = groupClass.replace('group-', '');
        const groupCheckbox = document.getElementById('group_' + groupSlug);
        const tableCheckboxes = document.querySelectorAll('.' + groupClass);
        
        let allChecked = true;
        let anyChecked = false;
        
        tableCheckboxes.forEach(cb => {
            if (cb.checked) {
                anyChecked = true;
            } else {
                allChecked = false;
            }
        });
        
        groupCheckbox.checked = allChecked;
        groupCheckbox.indeterminate = anyChecked && !allChecked;
    }
}

// Update selected count
function updateSelectedCount() {
    const count = document.querySelectorAll('.table-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

// Form validation
document.getElementById('exportForm').addEventListener('submit', function(e) {
    const checkedTables = document.querySelectorAll('.table-checkbox:checked');
    
    if (checkedTables.length === 0) {
        e.preventDefault();
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'No Tables Selected',
                text: 'Please select at least one table to export!',
                confirmButtonColor: '#FF6B00'
            });
        } else {
            alert('Please select at least one table to export!');
        }
        return false;
    }
    
    const includeStructure = document.getElementById('include_structure').checked;
    const includeData = document.getElementById('include_data').checked;
    
    if (!includeStructure && !includeData) {
        e.preventDefault();
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'No Options Selected',
                text: 'Please select at least one option: Structure or Data!',
                confirmButtonColor: '#FF6B00'
            });
        } else {
            alert('Please select at least one option: Structure or Data!');
        }
        return false;
    }
    
    // Show loading state
    const exportBtn = document.getElementById('exportBtn');
    exportBtn.disabled = true;
    exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';
    
    // Show loading alert if SweetAlert is available
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Exporting Database',
            html: 'Please wait while we prepare your export file...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Close SweetAlert and reset button after 3 seconds (assuming download started)
        setTimeout(() => {
            Swal.close();
            exportBtn.disabled = false;
            exportBtn.innerHTML = '<i class="fas fa-download me-2"></i><span>Export Database</span>';
        }, 3000);
    }
});
</script>
@endpush
