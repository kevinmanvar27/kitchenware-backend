@extends('frontend.layouts.app')

@section('title', 'All Pages')

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
    
    /* Page List Styles */
    .pages-container {
        padding: 2rem 0 4rem;
        background: #f8f9fa;
    }
    
    .page-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .page-card:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        border-color: var(--primary-color);
    }
    
    .page-card-link {
        display: block;
        padding: 1.5rem 2rem;
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
    }
    
    .page-card-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
    }
    
    .page-card:hover .page-card-link::before {
    }
    
    .page-card-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .page-card:hover .page-card-icon {
        box-shadow: 0 5px 20px rgba(var(--primary-rgb), 0.4);
    }
    
    .page-card-content {
        flex: 1;
        min-width: 0;
    }
    
    .page-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 0.5rem;
    }
    
    .page-card:hover .page-card-title {
        color: var(--primary-color);
    }
    
    .page-card-excerpt {
        color: var(--text-muted-color);
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .page-card-arrow {
        color: var(--primary-color);
        font-size: 1.25rem;
    }
    
    .page-card:hover .page-card-arrow {
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }
    
    .empty-state-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
    
    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 0.75rem;
    }
    
    .empty-state p {
        color: var(--text-muted-color);
        margin-bottom: 1.5rem;
    }
    
    .empty-state .btn {
        padding: 0.75rem 2rem;
        border-radius: 50px;
        font-weight: 600;
    }
    
    .empty-state .btn:hover {
        box-shadow: 0 10px 25px rgba(var(--primary-rgb), 0.3);
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 2rem 0;
        }
        
        .page-header h1 {
            font-size: 1.75rem;
        }
        
        .page-card-link {
            padding: 1.25rem;
        }
        
        .page-card-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .page-card-title {
            font-size: 1.1rem;
        }
        
        .page-card:hover {
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
                <li class="breadcrumb-item active" aria-current="page">Pages</li>
            </ol>
        </nav>
        <h1>
            <i class="fas fa-file-alt me-2"></i> All Pages
        </h1>
    </div>
</div>

<!-- Pages Content -->
<div class="pages-container">
    <div class="container">
        @if($pages->count() > 0)
            <div class="row">
                @foreach($pages as $index => $page)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="page-card">
                            <a href="{{ route('frontend.page.show', $page->slug) }}" class="page-card-link">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="page-card-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="page-card-content">
                                        <h5 class="page-card-title">{{ $page->title }}</h5>
                                        <p class="page-card-excerpt">{{ Str::limit(strip_tags($page->content), 80) }}</p>
                                    </div>
                                    <div class="page-card-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3>No Pages Available</h3>
                <p>There are no pages to display at the moment. Please check back later.</p>
                <a href="{{ route('frontend.home') }}" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i> Back to Home
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

