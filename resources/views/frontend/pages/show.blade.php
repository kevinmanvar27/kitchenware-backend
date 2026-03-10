@extends('frontend.layouts.app')

@section('title', $page->title)

@push('styles')
<style>
    /* Page Header Styles */
    .page-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 3rem 0;
        margin-bottom: 0;
        position: relative;
        overflow: hidden;
    }
    
    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    
    .page-header h1 {
        color: white;
        font-weight: 700;
        margin: 0;
        position: relative;
        z-index: 1;
        font-size: 2.5rem;
    }
    
    .page-header .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .page-header .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }
    
    .page-header .breadcrumb-item a:hover {
        color: white;
    }
    
    .page-header .breadcrumb-item.active {
        color: rgba(255, 255, 255, 0.9);
    }
    
    .page-header .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.6);
    }
    
    /* Page Content Styles */
    .page-content-wrapper {
        background: #f8f9fa;
        padding: 3rem 0 4rem;
        min-height: 50vh;
    }
    
    .page-content-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .page-content-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }
    
    /* Content Typography */
    .page-content {
        color: var(--text-color);
        line-height: 1.8;
        font-size: 1.05rem;
    }
    
    .page-content h1,
    .page-content h2,
    .page-content h3,
    .page-content h4,
    .page-content h5,
    .page-content h6 {
        color: var(--text-color);
        font-weight: 600;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    
    .page-content h2 {
        font-size: 1.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid rgba(var(--primary-rgb), 0.1);
    }
    
    .page-content h3 {
        font-size: 1.5rem;
    }
    
    .page-content p {
        margin-bottom: 1.25rem;
    }
    
    .page-content a {
        color: var(--primary-color);
        text-decoration: none;
        border-bottom: 1px solid transparent;
    }
    
    .page-content a:hover {
        color: var(--secondary-color);
        border-bottom-color: var(--secondary-color);
    }
    
    .page-content ul,
    .page-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1.25rem;
    }
    
    .page-content li {
        margin-bottom: 0.5rem;
    }
    
    .page-content blockquote {
        border-left: 4px solid var(--primary-color);
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
        background: rgba(var(--primary-rgb), 0.05);
        border-radius: 0 8px 8px 0;
        font-style: italic;
    }
    
    .page-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem 0;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }
    
    .page-content img:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .page-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .page-content table th,
    .page-content table td {
        padding: 0.75rem 1rem;
        border: 1px solid #e9ecef;
    }
    
    .page-content table th {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-weight: 600;
    }
    
    .page-content table tr:nth-child(even) {
        background: #f8f9fa;
    }
    
    .page-content table tr:hover {
        background: rgba(var(--primary-rgb), 0.05);
    }
    
    /* Sidebar */
    .page-sidebar {
        position: sticky;
        top: 100px;
    }
    
    .sidebar-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .sidebar-card:hover {
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
    }
    
    .sidebar-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid rgba(var(--primary-rgb), 0.1);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .sidebar-card-title i {
        color: var(--primary-color);
    }
    
    .related-pages-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .related-pages-list li {
        margin-bottom: 0.5rem;
    }
    
    .related-pages-list a {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        border-radius: 8px;
        color: var(--text-color);
        text-decoration: none;
    }
    
    .related-pages-list a:hover {
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary-color);
    }
    
    .related-pages-list a i {
        color: var(--primary-color);
        font-size: 0.875rem;
    }
    
    /* Back Button */
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        color: var(--text-color);
        border: 2px solid #e9ecef;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 500;
        margin-top: 2rem;
    }
    
    .back-btn:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .back-btn i {
    }
    
    .back-btn:hover i {
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 2rem 0;
        }
        
        .page-header h1 {
            font-size: 1.75rem;
        }
        
        .page-content-wrapper {
            padding: 2rem 0 3rem;
        }
        
        .page-content-card {
            padding: 1.5rem;
            border-radius: 12px;
        }
        
        .page-content {
            font-size: 1rem;
        }
        
        .page-sidebar {
            position: relative;
            top: 0;
            margin-top: 2rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('frontend.home') }}">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('frontend.pages.index') }}">Pages</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
            </ol>
        </nav>
        <h1>
            <i class="fas fa-file-alt me-2"></i> {{ $page->title }}
        </h1>
    </div>
</div>

<!-- Page Content -->
<div class="page-content-wrapper">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="page-content-card">
                    <div class="page-content">
                        {!! $page->content !!}
                    </div>
                    
                    <a href="{{ route('frontend.pages.index') }}" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back to All Pages
                    </a>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="page-sidebar">
                    <!-- Quick Navigation -->
                    <div class="sidebar-card">
                        <h5 class="sidebar-card-title">
                            <i class="fas fa-compass"></i> Quick Navigation
                        </h5>
                        <ul class="related-pages-list">
                            <li>
                                <a href="{{ route('frontend.home') }}">
                                    <i class="fas fa-chevron-right"></i>
                                    Home
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('frontend.home') }}">
                                    <i class="fas fa-chevron-right"></i>
                                    Categories
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('frontend.cart.index') }}">
                                    <i class="fas fa-chevron-right"></i>
                                    Shopping Cart
                                </a>
                            </li>
                            @auth
                            <li>
                                <a href="{{ route('frontend.profile') }}">
                                    <i class="fas fa-chevron-right"></i>
                                    My Profile
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="sidebar-card">
                        <h5 class="sidebar-card-title">
                            <i class="fas fa-headset"></i> Need Help?
                        </h5>
                        <p class="text-muted mb-3">Have questions? We're here to help!</p>
                        @if(setting('contact_email'))
                        <a href="mailto:{{ setting('contact_email') }}" class="d-flex align-items-center gap-2 text-decoration-none mb-2" style="color: var(--primary-color);">
                            <i class="fas fa-envelope"></i>
                            {{ setting('contact_email') }}
                        </a>
                        @endif
                        @if(setting('contact_phone'))
                        <a href="tel:{{ setting('contact_phone') }}" class="d-flex align-items-center gap-2 text-decoration-none" style="color: var(--primary-color);">
                            <i class="fas fa-phone"></i>
                            {{ setting('contact_phone') }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth scroll for anchor links
        document.querySelectorAll('.page-content a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });
</script>
@endpush