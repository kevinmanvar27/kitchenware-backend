<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Site settings for JavaScript access -->
    <meta name="site-title" content="{{ setting('site_title', 'Frontend App') }}">
    <meta name="company-address" content="{{ setting('address', 'Company Address') }}">
    <meta name="company-email" content="{{ setting('company_email', 'company@example.com') }}">
    <meta name="company-phone" content="{{ setting('company_phone', '+1 (555) 123-4567') }}">
    
    <title>{{ setting('site_title', 'Frontend App') }} - {{ setting('tagline', 'Your Frontend Application') }}</title>
    
    <!-- Favicon -->
    @if(setting('favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('favicon')) }}">
    @else
        <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAQAAVzABAEAjBQAaDwYAWjUGAGE6CQBrQQ0ATS8dAFAzHgBhPBMARjMcAFE0HgBmQg8ARjMeAFI1HgBhQg4AUzceAGZDDwBpRg4Aa0gOAHBKDgBzTA4Afk0OAHRNDgCETQ4A">
    @endif
    
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Google Fonts for font styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    
    <!-- Custom Styles with dynamic settings from backend -->
    <style>
        /* ==================== CSS Variables from Backend Settings ==================== */
        :root {
            /* Color Settings */
            --theme-color: {{ setting('theme_color', '#007bff') }};
            --background-color: {{ setting('background_color', '#f8f9fa') }};
            --font-color: {{ setting('font_color', '#333333') }};
            --sidebar-text-color: {{ setting('sidebar_text_color', '#333333') }};
            --heading-text-color: {{ setting('heading_text_color', '#333333') }};
            --label-text-color: {{ setting('label_text_color', '#333333') }};
            --general-text-color: {{ setting('general_text_color', '#333333') }};
            --link-color: {{ setting('link_color', '#007bff') }};
            --link-hover-color: {{ setting('link_hover_color', '#0056b3') }};
            
            /* Font Style */
            --font-family: {{ setting('font_style', 'Arial, sans-serif') }};
            
            /* Element-wise Font Families */
            --h1-font-family: {{ setting('h1_font_family', 'Arial, sans-serif') }};
            --h2-font-family: {{ setting('h2_font_family', 'Arial, sans-serif') }};
            --h3-font-family: {{ setting('h3_font_family', 'Arial, sans-serif') }};
            --h4-font-family: {{ setting('h4_font_family', 'Arial, sans-serif') }};
            --h5-font-family: {{ setting('h5_font_family', 'Arial, sans-serif') }};
            --h6-font-family: {{ setting('h6_font_family', 'Arial, sans-serif') }};
            --body-font-family: {{ setting('body_font_family', 'Arial, sans-serif') }};
            
            /* Desktop Font Sizes */
            --desktop-h1-size: {{ setting('desktop_h1_size', 36) }}px;
            --desktop-h2-size: {{ setting('desktop_h2_size', 30) }}px;
            --desktop-h3-size: {{ setting('desktop_h3_size', 24) }}px;
            --desktop-h4-size: {{ setting('desktop_h4_size', 20) }}px;
            --desktop-h5-size: {{ setting('desktop_h5_size', 18) }}px;
            --desktop-h6-size: {{ setting('desktop_h6_size', 16) }}px;
            --desktop-body-size: {{ setting('desktop_body_size', 16) }}px;
            
            /* Tablet Font Sizes */
            --tablet-h1-size: {{ setting('tablet_h1_size', 32) }}px;
            --tablet-h2-size: {{ setting('tablet_h2_size', 28) }}px;
            --tablet-h3-size: {{ setting('tablet_h3_size', 22) }}px;
            --tablet-h4-size: {{ setting('tablet_h4_size', 18) }}px;
            --tablet-h5-size: {{ setting('tablet_h5_size', 16) }}px;
            --tablet-h6-size: {{ setting('tablet_h6_size', 14) }}px;
            --tablet-body-size: {{ setting('tablet_body_size', 14) }}px;
            
            /* Mobile Font Sizes */
            --mobile-h1-size: {{ setting('mobile_h1_size', 28) }}px;
            --mobile-h2-size: {{ setting('mobile_h2_size', 24) }}px;
            --mobile-h3-size: {{ setting('mobile_h3_size', 20) }}px;
            --mobile-h4-size: {{ setting('mobile_h4_size', 16) }}px;
            --mobile-h5-size: {{ setting('mobile_h5_size', 14) }}px;
            --mobile-h6-size: {{ setting('mobile_h6_size', 12) }}px;
            --mobile-body-size: {{ setting('mobile_body_size', 12) }}px;
            

        }
        
        /* ==================== Base Styles ==================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--background-color) !important;
            color: var(--general-text-color) !important;
            font-family: var(--font-family) !important;
            font-size: var(--desktop-body-size) !important;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* ==================== Typography with Backend Settings ==================== */
        h1, .h1 {
            font-size: var(--desktop-h1-size) !important;
            font-family: var(--h1-font-family) !important;
            color: var(--heading-text-color) !important;
            font-weight: 700;
            line-height: 1.2;
        }
        
        h2, .h2 {
            font-size: var(--desktop-h2-size) !important;
            font-family: var(--h2-font-family) !important;
            color: var(--heading-text-color) !important;
            font-weight: 600;
            line-height: 1.3;
        }
        
        h3, .h3 {
            font-size: var(--desktop-h3-size) !important;
            font-family: var(--h3-font-family) !important;
            color: var(--heading-text-color) !important;
            font-weight: 600;
            line-height: 1.3;
        }
        
        h4, .h4 {
            font-size: var(--desktop-h4-size) !important;
            font-family: var(--h4-font-family) !important;
            color: var(--heading-text-color) !important;
            font-weight: 500;
            line-height: 1.4;
        }
        
        h5, .h5 {
            font-size: var(--desktop-h5-size) !important;
            font-family: var(--h5-font-family) !important;
            color: var(--heading-text-color) !important;
            font-weight: 500;
            line-height: 1.4;
        }
        
        h6, .h6 {
            font-size: var(--desktop-h6-size) !important;
            font-family: var(--h6-font-family) !important;
            color: var(--heading-text-color) !important;
            font-weight: 500;
            line-height: 1.5;
        }
        
        p, .lead, .general-text {
            font-size: var(--desktop-body-size) !important;
            font-family: var(--body-font-family) !important;
            color: var(--general-text-color) !important;
        }
        
        /* Label Text Color */
        label, .label-text, .form-label {
            color: var(--label-text-color) !important;
            font-weight: 500;
        }
        
        /* Sidebar Text Color */
        .sidebar-text, .nav-sidebar .nav-link {
            color: var(--sidebar-text-color) !important;
        }
        
        /* ==================== Link Styles ==================== */
        a {
            color: var(--link-color) !important;
            text-decoration: none;
            position: relative;
            transition: color 0.3s ease;
        }
        
        a:hover {
            color: var(--link-hover-color) !important;
        }
        
        /* Override link color for buttons - buttons should control their own colors */
        a.btn {
            color: inherit;
        }
        
        a.btn-theme,
        a.btn-theme:hover,
        a.btn-theme:focus {
            color: white !important;
        }
        
        a.btn-outline-theme {
            color: var(--theme-color) !important;
        }
        
        a.btn-outline-theme:hover,
        a.btn-outline-theme:focus {
            color: white !important;
        }
        
        a.btn-light,
        a.btn-light:hover,
        a.btn-light:focus {
            color: var(--theme-color) !important;
        }
        
        a.btn-outline-light {
            color: white !important;
        }
        
        a.btn-outline-light:hover,
        a.btn-outline-light:focus {
            color: var(--theme-color) !important;
        }
        
        /* Underline effect for links */
        .animated-link {
            position: relative;
            display: inline-block;
        }
        
        .animated-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--link-hover-color);
        }
        
        .animated-link:hover::after {
            width: 100%;
        }
        
        /* ==================== Button Styles ==================== */
        .btn-theme {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            color: white !important;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .btn-theme::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            z-index: -1;
            transition: left 0.5s ease;
        }
        
        .btn-theme:hover {
            background-color: var(--link-hover-color) !important;
            border-color: var(--link-hover-color) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-theme:hover::before {
            left: 100%;
        }
        
        .btn-theme:active {
            transform: translateY(0);
        }
        
        /* Ensure button text and icons stay white on hover */
        .btn-theme:hover,
        .btn-theme:hover i,
        .btn-theme:hover span,
        .btn-theme:focus,
        .btn-theme:focus i,
        .btn-theme:focus span {
            color: white !important;
        }
        
        .btn-outline-theme {
            border-color: var(--theme-color) !important;
            color: var(--theme-color) !important;
            background-color: transparent !important;
            transition: all 0.3s ease;
        }
        
        .btn-outline-theme:hover {
            background-color: var(--theme-color) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            transform: translateY(-2px);
        }
        
        /* Ensure outline button text and icons change color on hover */
        .btn-outline-theme:hover i,
        .btn-outline-theme:hover span,
        .btn-outline-theme:focus,
        .btn-outline-theme:focus i,
        .btn-outline-theme:focus span {
            color: white !important;
        }
        
        .btn-outline-theme:active {
            transform: translateY(0);
        }
        
        /* Outline Secondary Button */
        .btn-outline-secondary {
            transition: all 0.3s ease;
        }
        
        .btn-outline-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .btn-outline-secondary:hover i,
        .btn-outline-secondary:hover span {
            color: white !important;
        }
        
        .btn-outline-secondary:active {
            transform: translateY(0);
        }
        
        /* Ripple effect for buttons */
        .btn-ripple {
            position: relative;
            /* overflow: hidden; */
        }
        
        .btn-ripple .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            pointer-events: none;
            transform: scale(0);
            animation: ripple-animation 0.6s ease-out forwards;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* ==================== Card Styles ==================== */
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .category-card, .product-card {
            position: relative;
            overflow: hidden;
        }
        
        .category-card::before, .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--theme-color), var(--link-hover-color));
            z-index: 10;
        }
        
        .card-img-top {
        }
        
        /* Product link styling */
        .product-link {
            color: var(--heading-text-color) !important;
        }
        
        .product-link:hover {
            color: var(--theme-color) !important;
        }
        
        /* ==================== Header Styles ==================== */
        .site-header {
            background-color: #ffffff;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .site-header.scrolled {
            padding: 0.5rem 0 !important;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
        }
        
        .navbar-brand, .navbar-nav .nav-link {
            color: var(--sidebar-text-color) !important;
            position: relative;
        }
        
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            /* left: 50%; */
            background-color: var(--theme-color);
        }
        
        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--theme-color) !important;
        }
        
        /* ==================== Footer Styles ==================== */
        .site-footer {
            background-color: var(--background-color);
            border-top: 1px solid #dee2e6;
            padding: 2rem 0;
            margin-top: auto;
        }
        
        .footer-logo {
            max-height: 40px;
        }
        
        .footer-logo:hover {
        }
        
        /* Social icons */
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(0, 123, 255, 0.1);
            color: var(--theme-color) !important;
            margin: 0 5px;
        }
        
        .social-icon:hover {
            background-color: var(--theme-color);
            color: white !important;
        }
        
        /* ==================== Form Styles ==================== */
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--theme-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }
        
        /* ==================== Badge Styles ==================== */
        .bg-theme {
            background-color: var(--theme-color) !important;
        }
        
        .badge {
        }
        
        .badge:hover {
        }
        
        /* ==================== Cart Badge ==================== */
        .cart-count {
        }
        
        /* ==================== Wishlist Badge ==================== */
        .wishlist-count {
        }
        
        /* Wishlist button active state */
        .wishlist-btn.active i {
            color: #dc3545 !important;
        }
        
        /* ==================== Loading Spinner ==================== */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
        }
        
        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(0, 123, 255, 0.2);
            border-top-color: var(--theme-color);
            border-radius: 50%;
        }
        
        /* ==================== Toast Styles ==================== */
        .toast {
        }
        
        /* ==================== Scroll to Top Button ==================== */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background-color: var(--theme-color);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
        }
        
        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .scroll-to-top:hover {
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.5);
        }
        
        /* ==================== Page Transition ==================== */
        .page-transition {
        }
        
        /* ==================== Dropdown ==================== */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        
        .dropdown-item {
            padding: 0.6rem 1.2rem;
        }
        
        .dropdown-item:hover {
            background-color: rgba(0, 123, 255, 0.1);
            color: var(--theme-color) !important;
        }
        
        /* ==================== Alert ==================== */
        .alert {
        }
        
        /* ==================== Hover Effects ==================== */
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }
        
        .hover-scale {
            transition: transform 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: scale(1.05);
        }
        
        .hover-glow {
            transition: box-shadow 0.3s ease;
        }
        
        .hover-glow:hover {
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.4);
        }
        
        /* ==================== Outline Light Button (Hero Section) ==================== */
        .btn-outline-light {
            transition: all 0.3s ease;
        }
        
        .btn-outline-light:hover {
            background-color: white !important;
            color: var(--theme-color) !important;
            transform: translateY(-2px);
        }
        
        .btn-outline-light:hover i,
        .btn-outline-light:hover span {
            color: var(--theme-color) !important;
        }
        
        /* ==================== Light Button (Hero Section) ==================== */
        .btn-light {
            transition: all 0.3s ease;
        }
        
        .btn-light:hover {
            background-color: #f8f9fa !important;
            color: var(--theme-color) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }
        
        .btn-light:hover i,
        .btn-light:hover span {
            color: var(--theme-color) !important;
        }
        
        /* ==================== Card Footer Button Specific Styles ==================== */
        .card-footer .btn-theme {
            font-weight: 500;
        }
        
        .card-footer .btn-theme:hover {
            transform: translateY(-2px);
        }
        
        .card-footer .btn-theme:hover i {
            color: white !important;
        }
        
        /* ==================== Mobile Menu ==================== */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            height: 100vh;
            background: white;
            z-index: 1050;
            box-shadow: 5px 0 30px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            overflow-y: auto;
        }
        
        .mobile-menu.active {
            left: 0;
        }
        
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
        }
        
        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        /* ==================== Responsive Typography ==================== */
        @media (max-width: 992px) {
            h1, .h1 { font-size: var(--tablet-h1-size) !important; }
            h2, .h2 { font-size: var(--tablet-h2-size) !important; }
            h3, .h3 { font-size: var(--tablet-h3-size) !important; }
            h4, .h4 { font-size: var(--tablet-h4-size) !important; }
            h5, .h5 { font-size: var(--tablet-h5-size) !important; }
            h6, .h6 { font-size: var(--tablet-h6-size) !important; }
            body, p, .lead { font-size: var(--tablet-body-size) !important; }
        }
        
        @media (max-width: 768px) {
            h1, .h1 { font-size: var(--mobile-h1-size) !important; }
            h2, .h2 { font-size: var(--mobile-h2-size) !important; }
            h3, .h3 { font-size: var(--mobile-h3-size) !important; }
            h4, .h4 { font-size: var(--mobile-h4-size) !important; }
            h5, .h5 { font-size: var(--mobile-h5-size) !important; }
            h6, .h6 { font-size: var(--mobile-h6-size) !important; }
            body, p, .lead { font-size: var(--mobile-body-size) !important; }
            
            .scroll-to-top {
                bottom: 20px;
                right: 20px;
                width: 40px;
                height: 40px;
            }
        }
        
        /* ==================== Skeleton Loading ==================== */
        .skeleton {
            background: #e0e0e0;
            border-radius: 4px;
        }
        
        /* ==================== Image Lazy Load ==================== */
        img.lazy-loaded {
        }
        
        /* ==================== Custom Scrollbar ==================== */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--theme-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--link-hover-color);
        }
        
        /* ==================== Selection Color ==================== */
        ::selection {
            background-color: var(--theme-color);
            color: white;
        }
        
        /* ==================== Hero Section ==================== */
        .hero-section {
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        
        /* ==================== Policy Content Styles ==================== */
        .policy-content {
            font-family: var(--body-font-family);
            font-size: var(--desktop-body-size);
            line-height: 1.8;
            color: var(--general-text-color);
        }
        
        .policy-content h1,
        .policy-content h2,
        .policy-content h3,
        .policy-content h4,
        .policy-content h5,
        .policy-content h6 {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: var(--heading-text-color);
        }
        
        .policy-content h1 { font-size: var(--desktop-h1-size); font-family: var(--h1-font-family); }
        .policy-content h2 { font-size: var(--desktop-h2-size); font-family: var(--h2-font-family); }
        .policy-content h3 { font-size: var(--desktop-h3-size); font-family: var(--h3-font-family); }
        .policy-content h4 { font-size: var(--desktop-h4-size); font-family: var(--h4-font-family); }
        .policy-content h5 { font-size: var(--desktop-h5-size); font-family: var(--h5-font-family); }
        .policy-content h6 { font-size: var(--desktop-h6-size); font-family: var(--h6-font-family); }
        
        .policy-content p {
            margin-bottom: 1rem;
        }
        
        .policy-content ul,
        .policy-content ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        
        .policy-content li {
            margin-bottom: 0.5rem;
        }
        
        .policy-content a {
            color: var(--link-color);
            text-decoration: none;
        }
        
        .policy-content a:hover {
            color: var(--link-hover-color);
            text-decoration: underline;
        }
        
        .policy-content strong,
        .policy-content b {
            font-weight: 600;
        }
        
        .policy-content blockquote {
            border-left: 4px solid var(--theme-color);
            padding-left: 1rem;
            margin: 1rem 0;
            font-style: italic;
            color: #666;
        }
        
        .policy-content table {
            width: 100%;
            margin-bottom: 1rem;
            border-collapse: collapse;
        }
        
        .policy-content table th,
        .policy-content table td {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
        }
        
        .policy-content table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        /* Tablet responsive */
        @media (max-width: 991px) {
            .policy-content {
                font-size: var(--tablet-body-size);
            }
            .policy-content h1 { font-size: var(--tablet-h1-size); }
            .policy-content h2 { font-size: var(--tablet-h2-size); }
            .policy-content h3 { font-size: var(--tablet-h3-size); }
            .policy-content h4 { font-size: var(--tablet-h4-size); }
            .policy-content h5 { font-size: var(--tablet-h5-size); }
            .policy-content h6 { font-size: var(--tablet-h6-size); }
        }
        
        /* Mobile responsive */
        @media (max-width: 767px) {
            .policy-content {
                font-size: var(--mobile-body-size);
            }
            .policy-content h1 { font-size: var(--mobile-h1-size); }
            .policy-content h2 { font-size: var(--mobile-h2-size); }
            .policy-content h3 { font-size: var(--mobile-h3-size); }
            .policy-content h4 { font-size: var(--mobile-h4-size); }
            .policy-content h5 { font-size: var(--mobile-h5-size); }
            .policy-content h6 { font-size: var(--mobile-h6-size); }
        }
    </style>
    
    @yield('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="d-flex justify-content-between align-items-center mb-4">
            @if(setting('header_logo'))
                <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title', 'Frontend App') }}" class="rounded" height="40">
            @else
                <h5 class="mb-0 fw-bold heading-text">{{ setting('site_title', 'Frontend App') }}</h5>
            @endif
            <button class="btn btn-link text-dark p-0" id="closeMobileMenu">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>
        <nav>
            <ul class="list-unstyled">
                <li class="mb-3">
                    <a class="nav-link px-0" href="/">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                </li>
                @php
                    $mobileActivePages = \App\Models\Page::where('active', true)
                        ->orderBy('priority', 'asc')
                        ->get();
                @endphp
                @foreach($mobileActivePages as $page)
                    @if($page->title != 'Home')
                        <li class="mb-3">
                            <a class="nav-link px-0" href="{{ route('frontend.page.show', $page->slug) }}">
                                <i class="fas fa-file-alt me-2"></i>{{ $page->title }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>
        @auth
        <hr>
        <div class="mt-3">
            <a href="{{ route('frontend.profile') }}" class="btn btn-theme w-100 mb-2">
                <i class="fas fa-user me-2"></i>My Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-theme w-100">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
            </form>
        </div>
        @else
        <hr>
        <div class="mt-3">
            <a href="{{ route('login') }}" class="btn btn-theme w-100 mb-2">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
            <a href="{{ route('frontend.register') }}" class="btn btn-outline-theme w-100">
                <i class="fas fa-user-plus me-2"></i>Register
            </a>
        </div>
        @endauth
    </div>
    
    <!-- Header -->
    <header class="site-header py-3" id="siteHeader">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Mobile Menu Toggle -->
                <button class="btn btn-link text-dark d-md-none p-0 me-3" id="mobileMenuToggle">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
                
                <div class="d-flex align-items-center">
                    @if(setting('header_logo'))
                        <a href="/" class="hover-scale d-inline-block">
                            <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title', 'Frontend App') }}" class="rounded" height="50">
                        </a>
                    @else
                        <a href="/" class="text-decoration-none">
                            <h1 class="h4 mb-0 fw-bold heading-text">{{ setting('site_title', 'Frontend App') }}</h1>
                        </a>
                    @endif
                </div>
                
                <nav class="d-none d-md-block">
                    <ul class="navbar-nav flex-row">
                        <li class="nav-item me-3">
                            <a class="nav-link animated-link" href="/">Home</a>
                        </li>
                        @php
                            $activePages = \App\Models\Page::where('active', true)
                                ->orderBy('priority', 'asc')
                                ->get();
                        @endphp
                        
                        @foreach($activePages as $page)
                            @if($page->title != 'Home')
                                <li class="nav-item me-3">
                                    <a class="nav-link animated-link" href="{{ route('frontend.page.show', $page->slug) }}">
                                        {{ $page->title }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
                
                <div class="d-flex align-items-center">
                    @auth
                        <!-- Wishlist Icon -->
                        <a href="{{ route('frontend.wishlist.index') }}" class="btn btn-sm btn-outline-theme position-relative me-2 btn-ripple hover-lift" id="wishlistBtn">
                            <i class="fas fa-heart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-count">
                                {{ Auth::user()->wishlists()->count() }}
                            </span>
                        </a>
                        
                        <!-- Notifications Bell Icon -->
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-outline-theme position-relative btn-ripple hover-lift" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm notification-dropdown" aria-labelledby="notificationsDropdown" style="min-width: 320px; max-width: 400px; max-height: 400px; overflow-y: auto;">
                                <li><h6 class="dropdown-header fw-semibold">Notifications</h6></li>
                                
                                @forelse(auth()->user()->notifications->take(5) as $notification)
                                    <li class="notification-list-item">
                                        <a class="dropdown-item d-flex align-items-start py-2 notification-item {{ $notification->read ? '' : 'bg-light' }}" 
                                           href="#" 
                                           data-notification-id="{{ $notification->id }}" 
                                           data-notification-type="{{ $notification->type }}" 
                                           data-notification-data="{{ json_encode($notification->data) }}">
                                            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                @if($notification->type === 'proforma_invoice')
                                                    <i class="fas fa-file-invoice text-primary"></i>
                                                @elseif($notification->type === 'broadcast' || $notification->type === 'push')
                                                    <i class="fas fa-bullhorn text-warning"></i>
                                                @else
                                                    <i class="fas fa-bell text-info"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium small">{{ $notification->title ?? 'Notification' }}</div>
                                                <small class="text-secondary text-truncate d-block" style="max-width: 200px;">{{ $notification->message ?? '' }}</small>
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                            @if(!$notification->read)
                                                <span class="badge bg-primary rounded-pill ms-2 align-self-center">New</span>
                                            @endif
                                        </a>
                                    </li>
                                @empty
                                    <li class="no-notifications-item">
                                        <div class="dropdown-item text-center py-3">
                                            <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 1.5rem;"></i>
                                            <div class="fw-medium">No notifications</div>
                                            <small class="text-secondary">You're all caught up!</small>
                                        </div>
                                    </li>
                                @endforelse
                                
                                @if(auth()->user()->notifications->count() > 0)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-center fw-medium text-danger" href="#" id="clearAllNotifications">Clear all notifications</a></li>
                                @endif
                            </ul>
                        </div>
                        
                        <a href="{{ route('frontend.cart.index') }}" class="btn btn-sm btn-outline-theme position-relative me-3 btn-ripple hover-lift">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                                {{ Auth::user()->cartItems()->count() }}
                            </span>
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-theme dropdown-toggle btn-ripple" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1 d-none d-sm-inline"></i>
                                <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                                <i class="fas fa-user d-sm-none"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('frontend.profile') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('frontend.profile') }}#change-password"><i class="fas fa-key me-2"></i>Change Password</a></li>
                                <li><a class="dropdown-item" href="{{ route('frontend.wishlist.index') }}"><i class="fas fa-heart me-2"></i>My Wishlist</a></li>
                                <li><a class="dropdown-item" href="{{ route('frontend.cart.proforma.invoices') }}"><i class="fas fa-file-invoice me-2"></i>Proforma Invoice</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('frontend.wishlist.index') }}" class="btn btn-sm btn-outline-theme position-relative me-2 btn-ripple hover-lift" id="wishlistBtn">
                            <i class="fas fa-heart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-count">
                                0
                            </span>
                        </a>
                        <a href="{{ route('frontend.cart.index') }}" class="btn btn-sm btn-outline-theme position-relative me-3 btn-ripple hover-lift">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                                0
                            </span>
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-sm btn-theme btn-ripple hover-lift">
                            <i class="fas fa-sign-in-alt me-1 d-none d-sm-inline"></i>Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>
    
    <div id="app" class="flex-grow-1 page-transition">
        @yield('content')
    </div>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            @if(isset($vendor) && $vendor)
                {{-- Vendor Store Footer --}}
                @php
                    $vendorSettings = $vendor->store_settings ?? [];
                    $vendorSocialLinks = $vendor->social_links ?? [];
                @endphp
                <div class="row align-items-center">
                    <!-- Vendor Logo -->
                    <div class="col-lg-3 col-md-4 mb-3 mb-md-0 text-center text-md-start">
                        @if($vendor->store_logo_url)
                            <a href="{{ route('frontend.vendor.store', $vendor->store_slug) }}">
                                <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name }}" class="rounded footer-logo hover-scale" style="max-height: 50px;">
                            </a>
                        @else
                            <a href="{{ route('frontend.vendor.store', $vendor->store_slug) }}" class="text-decoration-none">
                                <span class="fw-bold general-text">{{ $vendor->store_name }}</span>
                            </a>
                        @endif
                    </div>
                    
                    <!-- Vendor Contact Info -->
                    <div class="col-lg-5 col-md-4 mb-3 mb-md-0 text-center">
                        @if($vendor->business_email || $vendor->business_phone)
                        <div class="d-flex justify-content-center flex-wrap gap-3">
                            @if($vendor->business_email)
                            <a href="mailto:{{ $vendor->business_email }}" class="text-muted text-decoration-none">
                                <i class="fas fa-envelope me-1"></i>{{ $vendor->business_email }}
                            </a>
                            @endif
                            @if($vendor->business_phone)
                            <a href="tel:{{ $vendor->business_phone }}" class="text-muted text-decoration-none">
                                <i class="fas fa-phone me-1"></i>{{ $vendor->business_phone }}
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                    
                    <!-- Vendor Social Icons -->
                    <div class="col-lg-4 col-md-4">
                        <div class="d-flex justify-content-md-end justify-content-center flex-wrap">
                            @php
                                // Get social links from store_settings or social_links
                                $facebook = $vendorSettings['social_facebook'] ?? ($vendorSocialLinks['facebook'] ?? null);
                                $twitter = $vendorSettings['social_twitter'] ?? ($vendorSocialLinks['twitter'] ?? null);
                                $instagram = $vendorSettings['social_instagram'] ?? ($vendorSocialLinks['instagram'] ?? null);
                                $linkedin = $vendorSocialLinks['linkedin'] ?? null;
                                $youtube = $vendorSettings['social_youtube'] ?? ($vendorSocialLinks['youtube'] ?? null);
                                $website = $vendorSettings['website'] ?? ($vendorSocialLinks['website'] ?? null);
                            @endphp
                            @if($facebook)
                            <a class="social-icon" href="{{ $facebook }}" target="_blank" data-bs-toggle="tooltip" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            @endif
                            @if($twitter)
                            <a class="social-icon" href="{{ $twitter }}" target="_blank" data-bs-toggle="tooltip" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            @endif
                            @if($instagram)
                            <a class="social-icon" href="{{ $instagram }}" target="_blank" data-bs-toggle="tooltip" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            @endif
                            @if($linkedin)
                            <a class="social-icon" href="{{ $linkedin }}" target="_blank" data-bs-toggle="tooltip" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            @endif
                            @if($youtube)
                            <a class="social-icon" href="{{ $youtube }}" target="_blank" data-bs-toggle="tooltip" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            @endif
                            @if($website)
                            <a class="social-icon" href="{{ $website }}" target="_blank" data-bs-toggle="tooltip" title="Website">
                                <i class="fas fa-globe"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Vendor Store Policies (if available) -->
                @if(!empty($vendorSettings['return_policy']) || !empty($vendorSettings['shipping_policy']) || !empty($vendorSettings['terms_conditions']))
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-center flex-wrap gap-3">
                            @if(!empty($vendorSettings['return_policy']))
                            <a href="#" class="text-muted text-decoration-none small" data-bs-toggle="modal" data-bs-target="#vendorReturnPolicyModal">
                                Return Policy
                            </a>
                            @endif
                            @if(!empty($vendorSettings['shipping_policy']))
                            <a href="#" class="text-muted text-decoration-none small" data-bs-toggle="modal" data-bs-target="#vendorShippingPolicyModal">
                                Shipping Policy
                            </a>
                            @endif
                            @if(!empty($vendorSettings['terms_conditions']))
                            <a href="#" class="text-muted text-decoration-none small" data-bs-toggle="modal" data-bs-target="#vendorTermsModal">
                                Terms & Conditions
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Vendor Copyright -->
                <hr class="my-3">
                <div class="text-center">
                    <small class="text-muted">
                        © {{ date('Y') }} {{ $vendor->store_name }}. All rights reserved.
                    </small>
                    <br>
                    <small class="text-muted">
                        <a href="{{ route('frontend.home') }}" class="text-muted text-decoration-none">
                            Powered by {{ setting('site_title', config('app.name', 'Laravel')) }}
                        </a>
                    </small>
                </div>
            @else
                {{-- Default Site Footer --}}
                <div class="row align-items-center">
                    <!-- Logo -->
                    <div class="col-lg-3 col-md-4 mb-3 mb-md-0 text-center text-md-start">
                        @if(setting('footer_logo'))
                            <img src="{{ asset('storage/' . setting('footer_logo')) }}" alt="{{ setting('site_title', 'Frontend App') }}" class="rounded footer-logo hover-scale">
                        @else
                            <span class="fw-bold general-text">{{ setting('site_title', config('app.name', 'Laravel')) }}</span>
                        @endif
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="col-lg-5 col-md-4 mb-3 mb-md-0 text-center">
                        @if(setting('company_email') || setting('company_phone'))
                        <div class="d-flex justify-content-center flex-wrap gap-3">
                            @if(setting('company_email'))
                            <a href="mailto:{{ setting('company_email') }}" class="text-muted text-decoration-none">
                                <i class="fas fa-envelope me-1"></i>{{ setting('company_email') }}
                            </a>
                            @endif
                            @if(setting('company_phone'))
                            <a href="tel:{{ setting('company_phone') }}" class="text-muted text-decoration-none">
                                <i class="fas fa-phone me-1"></i>{{ setting('company_phone') }}
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                    
                    <!-- Social Icons -->
                    <div class="col-lg-4 col-md-4">
                        <div class="d-flex justify-content-md-end justify-content-center flex-wrap">
                            @if(setting('facebook_url'))
                            <a class="social-icon" href="{{ setting('facebook_url') }}" target="_blank" data-bs-toggle="tooltip" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            @endif
                            @if(setting('twitter_url'))
                            <a class="social-icon" href="{{ setting('twitter_url') }}" target="_blank" data-bs-toggle="tooltip" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            @endif
                            @if(setting('instagram_url'))
                            <a class="social-icon" href="{{ setting('instagram_url') }}" target="_blank" data-bs-toggle="tooltip" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            @endif
                            @if(setting('linkedin_url'))
                            <a class="social-icon" href="{{ setting('linkedin_url') }}" target="_blank" data-bs-toggle="tooltip" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            @endif
                            @if(setting('youtube_url'))
                            <a class="social-icon" href="{{ setting('youtube_url') }}" target="_blank" data-bs-toggle="tooltip" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            @endif
                            @if(setting('whatsapp_url'))
                            <a class="social-icon" href="{{ setting('whatsapp_url') }}" target="_blank" data-bs-toggle="tooltip" title="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Copyright -->
                <hr class="my-3">
                <div class="text-center">
                    <small class="text-muted">
                        {{ setting('footer_text', '© ' . date('Y') . ' ' . setting('site_title', config('app.name', 'Laravel')) . '. All rights reserved.') }}
                    </small>
                </div>
            @endif
        </div>
    </footer>
    
    {{-- Vendor Policy Modals --}}
    @if(isset($vendor) && $vendor)
        @php
            $vendorSettings = $vendor->store_settings ?? [];
        @endphp
        
        @if(!empty($vendorSettings['return_policy']))
        <!-- Return Policy Modal -->
        <div class="modal fade" id="vendorReturnPolicyModal" tabindex="-1" aria-labelledby="vendorReturnPolicyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="vendorReturnPolicyModalLabel">
                            <i class="fas fa-undo me-2 text-theme"></i>Return Policy - {{ $vendor->store_name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="policy-content">
                            {!! $vendorSettings['return_policy'] !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if(!empty($vendorSettings['shipping_policy']))
        <!-- Shipping Policy Modal -->
        <div class="modal fade" id="vendorShippingPolicyModal" tabindex="-1" aria-labelledby="vendorShippingPolicyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="vendorShippingPolicyModalLabel">
                            <i class="fas fa-shipping-fast me-2 text-theme"></i>Shipping Policy - {{ $vendor->store_name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="policy-content">
                            {!! $vendorSettings['shipping_policy'] !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if(!empty($vendorSettings['terms_conditions']))
        <!-- Terms & Conditions Modal -->
        <div class="modal fade" id="vendorTermsModal" tabindex="-1" aria-labelledby="vendorTermsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="vendorTermsModalLabel">
                            <i class="fas fa-file-contract me-2 text-theme"></i>Terms & Conditions - {{ $vendor->store_name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="policy-content">
                            {!! $vendorSettings['terms_conditions'] !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
    
    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Header scroll effect
        const header = document.getElementById('siteHeader');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Scroll to top button
        const scrollToTopBtn = document.getElementById('scrollToTop');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });
        
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        const closeMobileMenu = document.getElementById('closeMobileMenu');
        
        function openMobileMenu() {
            mobileMenu.classList.add('active');
            mobileMenuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileMenuFunc() {
            mobileMenu.classList.remove('active');
            mobileMenuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        mobileMenuToggle.addEventListener('click', openMobileMenu);
        closeMobileMenu.addEventListener('click', closeMobileMenuFunc);
        mobileMenuOverlay.addEventListener('click', closeMobileMenuFunc);
        
        // Ripple effect for buttons
        document.querySelectorAll('.btn-ripple').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = e.clientX - rect.left - size / 2 + 'px';
                ripple.style.top = e.clientY - rect.top - size / 2 + 'px';
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Function to show toast message with animation
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0 position-fixed';
            toast.style = 'top: 20px; right: 20px; z-index: 9999;';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = '<div class="d-flex"><div class="toast-body"><i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
            
            document.body.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', function() {
                document.body.removeChild(toast);
            });
        }
        
        // Function to update cart count in header
        function updateCartCount(count) {
            var cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(function(element) {
                element.textContent = count;
            });
        }
        
        // Function to update wishlist count in header
        function updateWishlistCount(count) {
            var wishlistCountElements = document.querySelectorAll('.wishlist-count');
            wishlistCountElements.forEach(function(element) {
                element.textContent = count;
            });
        }
        
        // ==================== Guest Wishlist Functions ====================
        // Function to get guest wishlist from localStorage
        function getGuestWishlist() {
            const wishlist = localStorage.getItem('guest_wishlist');
            return wishlist ? JSON.parse(wishlist) : [];
        }
        
        // Function to save guest wishlist to localStorage
        function saveGuestWishlist(wishlist) {
            localStorage.setItem('guest_wishlist', JSON.stringify(wishlist));
        }
        
        // Function to add item to guest wishlist
        function addToGuestWishlist(productId) {
            var wishlist = getGuestWishlist();
            
            // Check if already in wishlist
            var existingItemIndex = wishlist.findIndex(function(item) { 
                return item.product_id == productId; 
            });
            
            if (existingItemIndex === -1) {
                wishlist.push({
                    product_id: productId,
                    added_at: new Date().toISOString()
                });
                saveGuestWishlist(wishlist);
                return true;
            }
            return false;
        }
        
        // Function to remove item from guest wishlist
        function removeFromGuestWishlist(productId) {
            var wishlist = getGuestWishlist();
            wishlist = wishlist.filter(function(item) { 
                return item.product_id != productId; 
            });
            saveGuestWishlist(wishlist);
        }
        
        // Function to check if product is in guest wishlist
        function isInGuestWishlist(productId) {
            var wishlist = getGuestWishlist();
            return wishlist.some(function(item) { 
                return item.product_id == productId; 
            });
        }
        
        // Function to get guest wishlist count
        function getGuestWishlistCount() {
            return getGuestWishlist().length;
        }
        
        // Function to update guest wishlist count display
        function updateGuestWishlistCount() {
            var count = getGuestWishlistCount();
            updateWishlistCount(count);
        }
        
        // Function to clear guest wishlist from localStorage
        function clearGuestWishlist() {
            localStorage.removeItem('guest_wishlist');
        }
        
        // ==================== Guest Cart Functions ====================
        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(function(el) {
                el.textContent = count;
                
                if (count > 0) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            });
        }
        
        // Function to get guest cart from localStorage
        function getGuestCart() {
            const cart = localStorage.getItem('guest_cart');
            return cart ? JSON.parse(cart) : [];
        }
        
        // Function to save guest cart to localStorage
        function saveGuestCart(cart) {
            localStorage.setItem('guest_cart', JSON.stringify(cart));
        }
        
        // Function to add item to guest cart
        function addToGuestCart(productId, quantity, variationId) {
            quantity = quantity || 1;
            variationId = variationId || null;
            var cart = getGuestCart();
            
            // For variable products, match both product_id and variation_id
            var existingItemIndex = cart.findIndex(function(item) { 
                return item.product_id == productId && item.product_variation_id == variationId; 
            });
            
            if (existingItemIndex !== -1) {
                cart[existingItemIndex].quantity += quantity;
            } else {
                var cartItem = {
                    product_id: productId,
                    quantity: quantity,
                    added_at: new Date().toISOString()
                };
                if (variationId) {
                    cartItem.product_variation_id = variationId;
                }
                cart.push(cartItem);
            }
            
            saveGuestCart(cart);
            return cart;
        }
        
        // Function to get guest cart count
        function getGuestCartCount() {
            var cart = getGuestCart();
            return cart.reduce(function(total, item) { return total + item.quantity; }, 0);
        }
        
        // Function to update guest cart count display
        function updateGuestCartCount() {
            var count = getGuestCartCount();
            updateCartCount(count);
        }
        
        // Function to clear guest cart from localStorage
        function clearGuestCart() {
            localStorage.removeItem('guest_cart');
        }
        
        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('active');
        }
        
        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('active');
        }
        
        // Initialize cart and wishlist count on page load
        document.addEventListener('DOMContentLoaded', function() {
            @guest
                updateGuestCartCount();
                updateGuestWishlistCount();
                
                // Update wishlist button states for guest users
                document.querySelectorAll('.wishlist-btn').forEach(function(btn) {
                    const productId = btn.dataset.productId;
                    if (isInGuestWishlist(productId)) {
                        btn.classList.add('active');
                        btn.querySelector('i').classList.remove('far');
                        btn.querySelector('i').classList.add('fas');
                    }
                });
            @endguest
            
            @if(session('login_success'))
                clearGuestCart();
                clearGuestWishlist();
            @endif
        });
        
        // Handle Add to Cart buttons
        document.addEventListener('click', function(e) {
            // Handle Wishlist buttons
            if (e.target.classList.contains('wishlist-btn') || e.target.closest('.wishlist-btn')) {
                var button = e.target.classList.contains('wishlist-btn') ? e.target : e.target.closest('.wishlist-btn');
                var productId = button.dataset.productId;
                var icon = button.querySelector('i');
                
                button.disabled = true;
                
                @auth
                    // Check if already in wishlist
                    var isInWishlist = button.classList.contains('active');
                    
                    if (isInWishlist) {
                        // Remove from wishlist - use web route for authenticated users
                        fetch('/wishlist/remove/' + productId, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            credentials: 'same-origin'
                        })
                        .then(function(response) { return response.json(); })
                        .then(function(data) {
                            if (data.success) {
                                button.classList.remove('active');
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                                
                                // Update text if exists
                                var textSpan = button.querySelector('.wishlist-text');
                                if (textSpan) {
                                    textSpan.textContent = 'Add to Wishlist';
                                }
                                
                                showToast(data.message, 'success');
                                
                                // Update wishlist count
                                fetch('/wishlist/count', {
                                    headers: {
                                        'Accept': 'application/json'
                                    },
                                    credentials: 'same-origin'
                                })
                                .then(function(response) { return response.json(); })
                                .then(function(data) {
                                    if (data.success) {
                                        updateWishlistCount(data.wishlist_count);
                                    }
                                });
                            } else {
                                showToast(data.message, 'error');
                            }
                        })
                        .catch(function(error) {
                            console.error('Wishlist error:', error);
                            showToast('An error occurred while removing from wishlist.', 'error');
                        })
                        .finally(function() {
                            button.disabled = false;
                        });
                    } else {
                        // Add to wishlist - use web route for authenticated users
                        fetch('/wishlist/add/' + productId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            credentials: 'same-origin'
                        })
                        .then(function(response) { return response.json(); })
                        .then(function(data) {
                            if (data.success) {
                                button.classList.add('active');
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                                
                                // Update text if exists
                                var textSpan = button.querySelector('.wishlist-text');
                                if (textSpan) {
                                    textSpan.textContent = 'Remove from Wishlist';
                                }
                                
                                showToast(data.message, 'success');
                                
                                // Update wishlist count
                                fetch('/wishlist/count', {
                                    headers: {
                                        'Accept': 'application/json'
                                    },
                                    credentials: 'same-origin'
                                })
                                .then(function(response) { return response.json(); })
                                .then(function(data) {
                                    if (data.success) {
                                        updateWishlistCount(data.wishlist_count);
                                    }
                                });
                            } else {
                                showToast(data.message, 'error');
                            }
                        })
                        .catch(function(error) {
                            console.error('Wishlist error:', error);
                            showToast('An error occurred while adding to wishlist.', 'error');
                        })
                        .finally(function() {
                            button.disabled = false;
                        });
                    }
                @else
                    // Guest user - use localStorage
                    var isInWishlist = button.classList.contains('active');
                    
                    if (isInWishlist) {
                        removeFromGuestWishlist(productId);
                        button.classList.remove('active');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        showToast('Removed from wishlist', 'success');
                        updateGuestWishlistCount();
                    } else {
                        if (addToGuestWishlist(productId)) {
                            button.classList.add('active');
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            showToast('Added to wishlist', 'success');
                            updateGuestWishlistCount();
                        } else {
                            showToast('Already in wishlist', 'error');
                        }
                    }
                    
                    button.disabled = false;
                @endauth
            }
            
            // Handle Add to Cart buttons
            if (e.target.classList.contains('add-to-cart-btn') || e.target.closest('.add-to-cart-btn')) {
                var button = e.target.classList.contains('add-to-cart-btn') ? e.target : e.target.closest('.add-to-cart-btn');
                var productId = button.dataset.productId;
                var variationId = button.dataset.variationId || null;
                var quantity = parseInt(button.dataset.quantity) || 1;
                
                // Validate quantity
                if (quantity < 1) {
                    showToast('Quantity must be at least 1', 'error');
                    return;
                }
                
                button.disabled = true;
                var originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
                
                @auth
                    var requestData = { 
                        product_id: productId,
                        quantity: quantity
                    };
                    if (variationId) {
                        requestData.product_variation_id = variationId;
                    }
                    
                    fetch('{{ route("frontend.cart.add") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(requestData)
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.success) {
                            updateCartCount(data.cart_count);
                            showToast(data.message, 'success');
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(function(error) {
                        showToast('An error occurred while adding the product to cart.', 'error');
                    })
                    .finally(function() {
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
                @else
                    try {
                        addToGuestCart(productId, quantity, variationId);
                        updateGuestCartCount();
                        showToast('Product added to cart successfully!', 'success');
                    } catch (error) {
                        showToast('An error occurred while adding the product to cart.', 'error');
                    } finally {
                        button.disabled = false;
                        button.innerHTML = originalText;
                    }
                @endauth
            }
            
            // Handle Buy Now buttons
            if (e.target.classList.contains('buy-now-btn') || e.target.closest('.buy-now-btn')) {
                var button = e.target.classList.contains('buy-now-btn') ? e.target : e.target.closest('.buy-now-btn');
                var productId = button.dataset.productId;
                var variationId = button.dataset.variationId || null;
                var quantity = parseInt(button.dataset.quantity) || 1;
                
                // Validate quantity
                if (quantity < 1) {
                    showToast('Quantity must be at least 1', 'error');
                    return;
                }
                
                button.disabled = true;
                var originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                
                @auth
                    var requestData = { 
                        product_id: productId,
                        quantity: quantity
                    };
                    if (variationId) {
                        requestData.product_variation_id = variationId;
                    }
                    
                    fetch('{{ route("frontend.cart.add") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(requestData)
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.success) {
                            updateCartCount(data.cart_count);
                            window.location.href = '{{ route("frontend.cart.index") }}';
                        } else {
                            showToast(data.message, 'error');
                            button.disabled = false;
                            button.innerHTML = originalText;
                        }
                    })
                    .catch(function(error) {
                        showToast('An error occurred while adding the product to cart.', 'error');
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
                @else
                    try {
                        addToGuestCart(productId, quantity, variationId);
                        updateGuestCartCount();
                        window.location.href = '/unified-login';
                    } catch (error) {
                        showToast('An error occurred while adding the product to cart.', 'error');
                        button.disabled = false;
                        button.innerHTML = originalText;
                    }
                @endauth
            }
        });
        
        // Notification handling
        @auth
        (function() {
            const notificationDropdown = document.querySelector('.notification-dropdown');
            const clearAllBtn = document.getElementById('clearAllNotifications');
            const notificationItems = document.querySelectorAll('.notification-item');
            
            // Handle individual notification clicks (delete notification)
            notificationItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const notificationId = this.getAttribute('data-notification-id');
                    const listItem = this.closest('.notification-list-item');
                    
                    // Delete notification
                    deleteNotification(notificationId, listItem);
                });
            });
            
            // Clear all notifications
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    fetch('{{ route("frontend.notifications.destroy-all") }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update notification count to 0
                            updateNotificationCount(0);
                            // Remove all notification items from DOM
                            document.querySelectorAll('.notification-list-item').forEach(item => {
                                item.remove();
                            });
                            // Show "no notifications" message
                            showNoNotificationsMessage();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            }
            
            // Function to delete a specific notification
            function deleteNotification(notificationId, listItem) {
                fetch(`/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update notification count
                        updateNotificationCount(data.unread_count);
                        // Remove the notification item from DOM
                        if (listItem) {
                            listItem.remove();
                        }
                        // Check if there are any notifications left
                        const remainingItems = document.querySelectorAll('.notification-list-item');
                        if (remainingItems.length === 0) {
                            showNoNotificationsMessage();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
            
            // Function to show "no notifications" message
            function showNoNotificationsMessage() {
                const dropdown = document.querySelector('.notification-dropdown');
                if (!dropdown) return;
                
                const divider = dropdown.querySelector('.dropdown-divider');
                
                // Remove the clear all section
                if (divider) {
                    const dividerLi = divider.closest('li');
                    const clearAllLi = dividerLi?.nextElementSibling;
                    if (dividerLi) dividerLi.remove();
                    if (clearAllLi) clearAllLi.remove();
                }
                
                // Check if no-notifications message already exists
                if (!dropdown.querySelector('.no-notifications-item')) {
                    const noNotificationsHtml = `
                        <li class="no-notifications-item">
                            <div class="dropdown-item text-center py-3">
                                <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-medium">No notifications</div>
                                <small class="text-secondary">You're all caught up!</small>
                            </div>
                        </li>
                    `;
                    const headerLi = dropdown.querySelector('.dropdown-header')?.closest('li');
                    if (headerLi) {
                        headerLi.insertAdjacentHTML('afterend', noNotificationsHtml);
                    }
                }
            }
            
            // Function to update notification count
            function updateNotificationCount(count) {
                const countElement = document.querySelector('#notificationsDropdown .notification-count');
                if (count > 0) {
                    if (countElement) {
                        countElement.textContent = count;
                    } else {
                        const badge = document.createElement('span');
                        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count';
                        badge.textContent = count;
                        document.querySelector('#notificationsDropdown').appendChild(badge);
                    }
                } else {
                    if (countElement) {
                        countElement.remove();
                    }
                }
            }
        })();
        @endauth
    </script>
    
    @yield('scripts')
</body>
</html>
