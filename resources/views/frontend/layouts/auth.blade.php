<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', setting('site_title', 'Frontend App') . ' - ' . setting('tagline', 'Your Frontend Application'))</title>
    
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
    
    <!-- Custom Styles with dynamic settings -->
    <style>
        :root {
            /* Color settings */
            --theme-color: {{ setting('theme_color', '#007bff') }};
            --background-color: {{ setting('background_color', '#f8f9fa') }};
            --font-color: {{ setting('font_color', '#333333') }};
            --font-style: {{ setting('font_style', 'Arial, sans-serif') }};
            --link-color: {{ setting('link_color', '#007bff') }};
            --link-hover-color: {{ setting('link_hover_color', '#0056b3') }};
            --sidebar-text-color: {{ setting('sidebar_text_color', '#333333') }};
            --heading-text-color: {{ setting('heading_text_color', '#333333') }};
            --label-text-color: {{ setting('label_text_color', '#333333') }};
            --general-text-color: {{ setting('general_text_color', '#333333') }};
            
            /* Element-wise Font Families */
            --h1-font-family: {{ setting('h1_font_family', 'Arial, sans-serif') }};
            --h2-font-family: {{ setting('h2_font_family', 'Arial, sans-serif') }};
            --h3-font-family: {{ setting('h3_font_family', 'Arial, sans-serif') }};
            --h4-font-family: {{ setting('h4_font_family', 'Arial, sans-serif') }};
            --h5-font-family: {{ setting('h5_font_family', 'Arial, sans-serif') }};
            --h6-font-family: {{ setting('h6_font_family', 'Arial, sans-serif') }};
            --body-font-family: {{ setting('body_font_family', 'Arial, sans-serif') }};
            
            /* Font size settings for desktop */
            --desktop-h1-size: {{ setting('desktop_h1_size', 36) }}px;
            --desktop-h2-size: {{ setting('desktop_h2_size', 30) }}px;
            --desktop-h3-size: {{ setting('desktop_h3_size', 24) }}px;
            --desktop-h4-size: {{ setting('desktop_h4_size', 20) }}px;
            --desktop-h5-size: {{ setting('desktop_h5_size', 18) }}px;
            --desktop-h6-size: {{ setting('desktop_h6_size', 16) }}px;
            --desktop-body-size: {{ setting('desktop_body_size', 16) }}px;
            
            /* Font size settings for tablet */
            --tablet-h1-size: {{ setting('tablet_h1_size', 32) }}px;
            --tablet-h2-size: {{ setting('tablet_h2_size', 28) }}px;
            --tablet-h3-size: {{ setting('tablet_h3_size', 22) }}px;
            --tablet-h4-size: {{ setting('tablet_h4_size', 18) }}px;
            --tablet-h5-size: {{ setting('tablet-h5_size', 16) }}px;
            --tablet-h6-size: {{ setting('tablet_h6_size', 14) }}px;
            --tablet-body-size: {{ setting('tablet_body_size', 14) }}px;
            
            /* Font size settings for mobile */
            --mobile-h1-size: {{ setting('mobile_h1_size', 28) }}px;
            --mobile-h2-size: {{ setting('mobile_h2_size', 24) }}px;
            --mobile-h3-size: {{ setting('mobile_h3_size', 20) }}px;
            --mobile-h4-size: {{ setting('mobile_h4_size', 16) }}px;
            --mobile-h5-size: {{ setting('mobile_h5_size', 14) }}px;
            --mobile-h6-size: {{ setting('mobile_h6_size', 12) }}px;
            --mobile-body-size: {{ setting('mobile_body_size', 12) }}px;
        }
        
        body {
            background: linear-gradient(135deg, var(--theme-color) 0%, #ffffff 100%);
            color: var(--font-color) !important;
            font-family: var(--body-font-family) !important;
            font-size: var(--desktop-body-size) !important;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Animated background shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: var(--theme-color);
            border-radius: 50%;
            top: -150px;
            right: -150px;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            background: var(--theme-color);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            bottom: 10%;
            left: 5%;
            animation: float 8s ease-in-out infinite reverse;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            background: var(--theme-color);
            border-radius: 50%;
            top: 50%;
            right: 10%;
            animation: float 7s ease-in-out infinite;
        }
        
        .shape-4 {
            width: 250px;
            height: 250px;
            background: var(--theme-color);
            border-radius: 63% 37% 54% 46% / 55% 48% 52% 45%;
            bottom: -100px;
            right: 20%;
            animation: float 9s ease-in-out infinite;
        }
        
        .shape-5 {
            width: 180px;
            height: 180px;
            background: var(--theme-color);
            border-radius: 50%;
            top: 20%;
            left: -90px;
            animation: float 5s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }
        
        /* Typography with element-wise font families */
        h1, .h1 {
            font-family: var(--h1-font-family) !important;
            font-size: var(--desktop-h1-size) !important;
            color: var(--heading-text-color) !important;
        }
        
        h2, .h2 {
            font-family: var(--h2-font-family) !important;
            font-size: var(--desktop-h2-size) !important;
            color: var(--heading-text-color) !important;
        }
        
        h3, .h3 {
            font-family: var(--h3-font-family) !important;
            font-size: var(--desktop-h3-size) !important;
            color: var(--heading-text-color) !important;
        }
        
        h4, .h4 {
            font-family: var(--h4-font-family) !important;
            font-size: var(--desktop-h4-size) !important;
            color: var(--heading-text-color) !important;
        }
        
        h5, .h5 {
            font-family: var(--h5-font-family) !important;
            font-size: var(--desktop-h5-size) !important;
            color: var(--heading-text-color) !important;
        }
        
        h6, .h6 {
            font-family: var(--h6-font-family) !important;
            font-size: var(--desktop-h6-size) !important;
            color: var(--heading-text-color) !important;
        }
        
        p, .lead, .general-text {
            font-family: var(--body-font-family) !important;
            font-size: var(--desktop-body-size) !important;
        }
        
        /* Text color styles */
        .navbar-brand, .navbar-nav .nav-link {
            color: var(--font-color) !important;
        }
        
        .sidebar-text {
            color: var(--sidebar-text-color) !important;
        }
        
        .heading-text {
            color: var(--heading-text-color) !important;
        }
        
        .label-text {
            color: var(--label-text-color) !important;
        }
        
        .general-text {
            color: var(--general-text-color) !important;
        }
        
        /* Button styles */
        .btn-theme {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            color: white !important;
            position: relative;
            overflow: hidden;
        }
        
        .btn-theme:hover {
            background-color: var(--link-hover-color) !important;
            border-color: var(--link-hover-color) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-theme:active {
        }
        
        /* Ripple effect for buttons */
        .btn-theme::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
        }
        
        .btn-theme:hover::after {
            width: 300px;
            height: 300px;
        }
        
        /* Link styles */
        a {
            color: var(--link-color) !important;
        }
        
        a:hover {
            color: var(--link-hover-color) !important;
        }
        
        /* Font size styles for headings with higher specificity */
        h1, .h1 {
            font-size: var(--desktop-h1-size) !important;
        }
        
        h2, .h2 {
            font-size: var(--desktop-h2-size) !important;
        }
        
        h3, .h3 {
            font-size: var(--desktop-h3-size) !important;
        }
        
        h4, .h4 {
            font-size: var(--desktop-h4-size) !important;
        }
        
        h5, .h5 {
            font-size: var(--desktop-h5-size) !important;
        }
        
        h6, .h6 {
            font-size: var(--desktop-h6-size) !important;
        }
        
        p, .lead {
            font-size: var(--desktop-body-size) !important;
        }
        
        /* Responsive font sizes with higher specificity */
        @media (max-width: 992px) {
            h1, .h1 {
                font-size: var(--tablet-h1-size) !important;
            }
            
            h2, .h2 {
                font-size: var(--tablet-h2-size) !important;
            }
            
            h3, .h3 {
                font-size: var(--tablet-h3-size) !important;
            }
            
            h4, .h4 {
                font-size: var(--tablet-h4-size) !important;
            }
            
            h5, .h5 {
                font-size: var(--tablet-h5-size) !important;
            }
            
            h6, .h6 {
                font-size: var(--tablet-h6-size) !important;
            }
            
            body, p, .lead {
                font-size: var(--tablet-body-size) !important;
            }
        }
        
        @media (max-width: 768px) {
            h1, .h1 {
                font-size: var(--mobile-h1-size) !important;
            }
            
            h2, .h2 {
                font-size: var(--mobile-h2-size) !important;
            }
            
            h3, .h3 {
                font-size: var(--mobile-h3-size) !important;
            }
            
            h4, .h4 {
                font-size: var(--mobile-h4-size) !important;
            }
            
            h5, .h5 {
                font-size: var(--mobile-h5-size) !important;
            }
            
            h6, .h6 {
                font-size: var(--mobile-h6-size) !important;
            }
            
            body, p, .lead {
                font-size: var(--mobile-body-size) !important;
            }
        }
        
        /* Auth page specific styles */
        .auth-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 2rem 0;
            position: relative;
            z-index: 1;
        }
        
        /* Auth card animations */
        .auth-card {
            border-radius: 16px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        /* Form input styles */
        .form-control {
            border: 2px solid #e0e0e0;
        }
        
        .form-control:hover {
            border-color: var(--theme-color);
        }
        
        .form-control:focus {
            border-color: var(--theme-color);
            box-shadow: 0 0 0 4px rgba(var(--theme-color-rgb, 0, 123, 255), 0.1);
        }
        
        /* Form group styles */
        .form-group-animated {
        }
        
        /* Alert styles */
        .alert {
        }
        
        /* Password toggle */
        .toggle-password {
        }
        
        .toggle-password:hover {
            background-color: var(--theme-color);
            border-color: var(--theme-color);
            color: white;
        }
        
        .toggle-password i {
        }
        
        .toggle-password:hover i {
        }
        
        /* Logo */
        .auth-logo {
        }
        
        /* Title */
        .auth-title {
        }
        
        .auth-subtitle {
        }
        
        /* Checkbox */
        .form-check-input {
        }
        
        .form-check-input:checked {
            background-color: var(--theme-color);
            border-color: var(--theme-color);
        }
        
        /* Footer text */
        .auth-footer {
        }
        
        /* Loading spinner for form submission */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
        }
        
        .btn-loading span {
            visibility: hidden;
        }
        
        /* Input group */
        .input-group {
        }
        
        .input-group:focus-within {
        }
        
        .input-group:focus-within .form-control {
            border-color: var(--theme-color);
        }
        
        .input-group:focus-within .toggle-password {
            border-color: var(--theme-color);
        }
        
        /* Background decoration */
        .auth-bg-decoration {
            display: none; /* Disabled in favor of animated shapes */
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Animated Background Shapes -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
    </div>
    
    <div class="auth-wrapper">
        @yield('content')
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
