<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UnifiedLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\FirebaseController;
use App\Http\Controllers\Admin\FirebaseTestController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductAttributeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Controllers\Admin\ProformaInvoiceController;
use App\Http\Controllers\Admin\WithoutGstInvoiceController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorPaymentController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\RegisterController;
use App\Http\Controllers\Frontend\ShoppingCartController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Frontend\PageController as FrontendPageController;
use App\Http\Controllers\Frontend\AccountDeletionController;
use App\Http\Controllers\Frontend\NotificationController as FrontendNotificationController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Admin\ProductAnalyticsController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\FeatureSettingsController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\DatabaseExportImportController;

// Include vendor routes
require __DIR__.'/vendor.php';

// Redirect root URL based on frontend access settings
Route::get('/', function () {
    // Get the frontend access permission setting
    $setting = \App\Models\Setting::first();
    $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
    
    // For "Open for all", redirect to home page
    if ($accessPermission === 'open_for_all') {
        return redirect()->route('frontend.home');
    }
    
    // For other modes, redirect to login page
    return redirect()->route('login');
});

// Client Documentation Route
Route::get('client-doc', function () {
    return response()->file(public_path('client-doc/index.html'));
})->name('client.documentation');

/*
|--------------------------------------------------------------------------
| Unified Login Routes (Single Login Page for All User Types)
|--------------------------------------------------------------------------
|
| This login page handles authentication for all user types:
| - Admin (super_admin, admin, editor, staff)
| - Vendor (vendor owners)
| - Vendor Staff
| - Frontend Users (customers)
|
| After successful login, users are automatically redirected to their
| respective dashboards based on their role.
|
*/
Route::get('login', [UnifiedLoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [UnifiedLoginController::class, 'login'])->name('login.post');
Route::post('logout', [UnifiedLoginController::class, 'logout'])->name('logout');

// CSRF Token Refresh Route (prevents 419 errors on long forms)
Route::get('/csrf-refresh', function() {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');

// Frontend Registration Routes
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('frontend.register');
Route::post('register', [RegisterController::class, 'register'])->name('frontend.register.post');

// Account Deletion Routes (Public - No Auth Required)
Route::get('delete-account', [AccountDeletionController::class, 'showForm'])->name('account.delete.form');
Route::post('delete-account', [AccountDeletionController::class, 'deleteAccount'])->name('account.delete.submit');

// RazorpayX Webhook (Public - No Auth Required)
Route::post('webhooks/razorpayx', [VendorPaymentController::class, 'handleWebhook'])
    ->name('webhooks.razorpayx')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Public Frontend Routes (accessible based on settings)
Route::middleware('frontend.access')->group(function () {
    Route::get('/home', [FrontendController::class, 'index'])->name('frontend.home');
    Route::get('/category/{category:slug}', [FrontendController::class, 'showCategory'])->name('frontend.category.show');
    Route::get('/product/{product:slug}', [FrontendController::class, 'showProduct'])->name('frontend.product.show');
    // AJAX route for fetching subcategories
    Route::get('/frontend/category/{category}/subcategories', [FrontendController::class, 'getSubcategories'])->name('frontend.category.subcategories');
    
    // Vendor Store Page (within main frontend)
    Route::get('/store/{vendorSlug}', [FrontendController::class, 'vendorStore'])->name('frontend.vendor.store');
    
    // Vendor Product Page (same as regular product page, vendor context is handled in controller)
    Route::get('/store/{vendorSlug}/product/{product:slug}', [FrontendController::class, 'showProduct'])->name('frontend.vendor.product.show');
    
    // Frontend Pages Routes
    Route::get('/pages', [FrontendPageController::class, 'index'])->name('frontend.pages.index');
    Route::get('/page/{slug}', [FrontendPageController::class, 'show'])->name('frontend.page.show');
});

// Frontend Authenticated Routes
Route::middleware(['frontend.access', 'auth'])->group(function () {
    Route::get('/profile', [FrontendController::class, 'profile'])->name('frontend.profile');
    Route::post('/profile', [FrontendController::class, 'updateProfile'])->name('frontend.profile.update');
    Route::post('/profile/avatar', [FrontendController::class, 'updateAvatar'])->name('frontend.profile.avatar.update');
    Route::post('/profile/avatar/remove', [FrontendController::class, 'removeAvatar'])->name('frontend.profile.avatar.remove');
    Route::post('/profile/password', [FrontendController::class, 'changePassword'])->name('frontend.profile.password.change');
    
    // Shopping Cart Routes
    Route::get('/cart', [ShoppingCartController::class, 'index'])->name('frontend.cart.index');
    Route::post('/cart/add', [ShoppingCartController::class, 'addToCart'])->name('frontend.cart.add');
    Route::put('/cart/update/{id}', [ShoppingCartController::class, 'updateCart'])->name('frontend.cart.update');
    Route::delete('/cart/remove/{id}', [ShoppingCartController::class, 'removeFromCart'])->name('frontend.cart.remove');
    Route::get('/cart/count', [ShoppingCartController::class, 'getCartCount'])->name('frontend.cart.count');
    Route::post('/cart/migrate', [ShoppingCartController::class, 'migrateGuestCart'])->name('frontend.cart.migrate');
    Route::get('/cart/proforma-invoices', [ShoppingCartController::class, 'listProformaInvoices'])->name('frontend.cart.proforma.invoices');
    Route::get('/cart/proforma-invoice/{id}', [ShoppingCartController::class, 'getProformaInvoiceDetails'])->name('frontend.cart.proforma.invoice.details');
    Route::get('/cart/proforma-invoice/{id}/download-pdf', [ShoppingCartController::class, 'downloadProformaInvoicePDF'])->name('frontend.cart.proforma.invoice.download-pdf');
    Route::post('/cart/proforma-invoice/{id}/add-to-cart', [ShoppingCartController::class, 'addInvoiceToCart'])->name('frontend.cart.proforma.invoice.add-to-cart');
    Route::delete('/cart/proforma-invoice/{id}', [ShoppingCartController::class, 'deleteProformaInvoice'])->name('frontend.cart.proforma.invoice.delete');
    
    // Without GST Invoice Routes (Frontend)
    Route::get('/cart/without-gst-invoices', [ShoppingCartController::class, 'listWithoutGstInvoices'])->name('frontend.cart.without-gst.invoices');
    Route::get('/cart/without-gst-invoice/{id}', [ShoppingCartController::class, 'getWithoutGstInvoiceDetails'])->name('frontend.cart.without-gst.invoice.details');
    Route::get('/cart/without-gst-invoice/{id}/download-pdf', [ShoppingCartController::class, 'downloadWithoutGstInvoicePDF'])->name('frontend.cart.without-gst.invoice.download-pdf');

    // Coupon Routes
    Route::post('/cart/coupon/apply', [ShoppingCartController::class, 'applyCoupon'])->name('frontend.cart.coupon.apply');
    Route::post('/cart/coupon/remove', [ShoppingCartController::class, 'removeCoupon'])->name('frontend.cart.coupon.remove');
    Route::get('/cart/coupon/applied', [ShoppingCartController::class, 'getAppliedCoupon'])->name('frontend.cart.coupon.applied');

    // Wishlist Routes
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('frontend.wishlist.index');
    Route::post('/wishlist/add/{productId}', [WishlistController::class, 'add'])->name('frontend.wishlist.add');
    Route::delete('/wishlist/remove/{productId}', [WishlistController::class, 'remove'])->name('frontend.wishlist.remove');
    Route::delete('/wishlist/clear', [WishlistController::class, 'clear'])->name('frontend.wishlist.clear');
    Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('frontend.wishlist.count');
    Route::get('/wishlist/check/{productId}', [WishlistController::class, 'check'])->name('frontend.wishlist.check');
    Route::post('/wishlist/check-multiple', [WishlistController::class, 'checkMultiple'])->name('frontend.wishlist.checkMultiple');

    // Notification Routes
    Route::delete('/notifications/{id}', [FrontendNotificationController::class, 'destroy'])->name('frontend.notifications.destroy');
    Route::delete('/notifications', [FrontendNotificationController::class, 'destroyAll'])->name('frontend.notifications.destroy-all');
    Route::post('/notifications/{id}/mark-as-read', [FrontendNotificationController::class, 'markAsRead'])->name('frontend.notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [FrontendNotificationController::class, 'markAllAsRead'])->name('frontend.notifications.mark-all-as-read');

});

// Frontend Access Routes (available to guests as well)
Route::middleware('frontend.access')->group(function () {
    // Proforma Invoice Generation Route (available to guests)
    Route::get('/cart/proforma-invoice', [ShoppingCartController::class, 'generateProformaInvoice'])->name('frontend.cart.proforma.invoice');
});

// Dynamic CSS Route
Route::get('/css/dynamic.css', function () {
    $setting = \App\Models\Setting::first();
    
    // Font settings
    $fontColor = $setting && $setting->font_color ? $setting->font_color : '#333333';
    $fontStyle = $setting && $setting->font_style ? $setting->font_style : 'Arial, sans-serif';
    
    // Theme color settings
    $themeColor = $setting && $setting->theme_color ? $setting->theme_color : '#FF6B00';
    $backgroundColor = $setting && $setting->background_color ? $setting->background_color : '#FFFFFF';
    
    // Text color settings
    $sidebarTextColor = $setting && $setting->sidebar_text_color ? $setting->sidebar_text_color : '#333333';
    $headingTextColor = $setting && $setting->heading_text_color ? $setting->heading_text_color : '#333333';
    $labelTextColor = $setting && $setting->label_text_color ? $setting->label_text_color : '#333333';
    $generalTextColor = $setting && $setting->general_text_color ? $setting->general_text_color : '#333333';
    $linkColor = $setting && $setting->link_color ? $setting->link_color : '#333333';
    $linkHoverColor = $setting && $setting->link_hover_color ? $setting->link_hover_color : '#FF6B00';
    
    // Font size settings
    $desktopH1Size = $setting && $setting->desktop_h1_size ? $setting->desktop_h1_size : 36;
    $desktopH2Size = $setting && $setting->desktop_h2_size ? $setting->desktop_h2_size : 30;
    $desktopH3Size = $setting && $setting->desktop_h3_size ? $setting->desktop_h3_size : 24;
    $desktopH4Size = $setting && $setting->desktop_h4_size ? $setting->desktop_h4_size : 20;
    $desktopH5Size = $setting && $setting->desktop_h5_size ? $setting->desktop_h5_size : 18;
    $desktopH6Size = $setting && $setting->desktop_h6_size ? $setting->desktop_h6_size : 16;
    $desktopBodySize = $setting && $setting->desktop_body_size ? $setting->desktop_body_size : 16;
    
    $tabletH1Size = $setting && $setting->tablet_h1_size ? $setting->tablet_h1_size : 32;
    $tabletH2Size = $setting && $setting->tablet_h2_size ? $setting->tablet_h2_size : 28;
    $tabletH3Size = $setting && $setting->tablet_h3_size ? $setting->tablet_h3_size : 22;
    $tabletH4Size = $setting && $setting->tablet_h4_size ? $setting->tablet_h4_size : 18;
    $tabletH5Size = $setting && $setting->tablet_h5_size ? $setting->tablet_h5_size : 16;
    $tabletH6Size = $setting && $setting->tablet_h6_size ? $setting->tablet_h6_size : 14;
    $tabletBodySize = $setting && $setting->tablet_body_size ? $setting->tablet_body_size : 14;
    
    $mobileH1Size = $setting && $setting->mobile_h1_size ? $setting->mobile_h1_size : 28;
    $mobileH2Size = $setting && $setting->mobile_h2_size ? $setting->mobile_h2_size : 24;
    $mobileH3Size = $setting && $setting->mobile_h3_size ? $setting->mobile_h3_size : 20;
    $mobileH4Size = $setting && $setting->mobile_h4_size ? $setting->mobile_h4_size : 16;
    $mobileH5Size = $setting && $setting->mobile_h5_size ? $setting->mobile_h5_size : 14;
    $mobileH6Size = $setting && $setting->mobile_h6_size ? $setting->mobile_h6_size : 12;
    $mobileBodySize = $setting && $setting->mobile_body_size ? $setting->mobile_body_size : 12;
    
    $css = ":root { 
        --font-color: {$fontColor}; 
        --font-style: {$fontStyle};
        --theme-color: {$themeColor};
        --background-color: {$backgroundColor};
        --sidebar-text-color: {$sidebarTextColor};
        --heading-text-color: {$headingTextColor};
        --label-text-color: {$labelTextColor};
        --general-text-color: {$generalTextColor};
        --link-color: {$linkColor};
        --link-hover-color: {$linkHoverColor};
        
        /* Font size settings */
        --desktop-h1-size: {$desktopH1Size}px;
        --desktop-h2-size: {$desktopH2Size}px;
        --desktop-h3-size: {$desktopH3Size}px;
        --desktop-h4-size: {$desktopH4Size}px;
        --desktop-h5-size: {$desktopH5Size}px;
        --desktop-h6-size: {$desktopH6Size}px;
        --desktop-body-size: {$desktopBodySize}px;
        
        --tablet-h1-size: {$tabletH1Size}px;
        --tablet-h2-size: {$tabletH2Size}px;
        --tablet-h3-size: {$tabletH3Size}px;
        --tablet-h4-size: {$tabletH4Size}px;
        --tablet-h5-size: {$tabletH5Size}px;
        --tablet-h6-size: {$tabletH6Size}px;
        --tablet-body-size: {$tabletBodySize}px;
        
        --mobile-h1-size: {$mobileH1Size}px;
        --mobile-h2-size: {$mobileH2Size}px;
        --mobile-h3-size: {$mobileH3Size}px;
        --mobile-h4-size: {$mobileH4Size}px;
        --mobile-h5-size: {$mobileH5Size}px;
        --mobile-h6-size: {$mobileH6Size}px;
        --mobile-body-size: {$mobileBodySize}px;
    }";
    
    return response($css, 200)->header('Content-Type', 'text/css');
});

// Admin Routes (protected by admin middleware - only super_admin, admin, editor, staff can access)
Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard - accessible to all admin users (basic access)
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/admin/color-palette', function () {
        return view('admin.color-palette');
    })->name('admin.color-palette');
    
    // Settings Routes - requires manage_settings permission
    Route::middleware(['permission:manage_settings'])->group(function () {
        Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings');
        Route::post('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
        Route::post('/admin/settings/reset', [SettingsController::class, 'reset'])->name('admin.settings.reset');
    });
    
    // Database Management Routes - requires manage_database permission
    Route::middleware(['permission:manage_database'])->group(function () {
        Route::post('/admin/settings/database/clean', [SettingsController::class, 'cleanDatabase'])->name('admin.settings.database.clean');
        Route::post('/admin/settings/database/export', [SettingsController::class, 'exportDatabase'])->name('admin.settings.database.export');
    });
    
    // Database Export/Import Routes - requires manage_database permission
    Route::middleware(['permission:manage_database'])->prefix('admin/database')->group(function () {
        // Export Routes
        Route::get('/export', [DatabaseExportImportController::class, 'exportIndex'])->name('admin.database.export.index');
        Route::post('/export', [DatabaseExportImportController::class, 'export'])->name('admin.database.export');
        Route::get('/export/download/{filename}', [DatabaseExportImportController::class, 'downloadExport'])->name('admin.database.export.download');
        Route::delete('/export/delete/{filename}', [DatabaseExportImportController::class, 'deleteExport'])->name('admin.database.export.delete');
        
        // Import Routes
        Route::get('/import', [DatabaseExportImportController::class, 'importIndex'])->name('admin.database.import.index');
        Route::post('/import', [DatabaseExportImportController::class, 'import'])->name('admin.database.import');
    });
    
    // Subscription Plan Routes - requires subscription plan permissions
    Route::prefix('admin/subscription-plans')->middleware(['permission:viewAny_subscription_plan'])->group(function () {
        Route::get('/', [SubscriptionPlanController::class, 'index'])->name('admin.subscription-plans.index');
        Route::get('/statistics', [SubscriptionPlanController::class, 'statistics'])->name('admin.subscription-plans.statistics');
        Route::post('/', [SubscriptionPlanController::class, 'store'])->name('admin.subscription-plans.store');
        Route::put('/{plan}', [SubscriptionPlanController::class, 'update'])->name('admin.subscription-plans.update');
        Route::delete('/{plan}', [SubscriptionPlanController::class, 'destroy'])->name('admin.subscription-plans.destroy');
        Route::post('/{plan}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus'])->name('admin.subscription-plans.toggle-status');
        Route::post('/update-order', [SubscriptionPlanController::class, 'updateOrder'])->name('admin.subscription-plans.update-order');
    });
    
    // Profile Routes - accessible to all admin users (own profile management)
    Route::get('/admin/profile', [ProfileController::class, 'show'])->name('admin.profile');
    Route::post('/admin/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/admin/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('admin.profile.avatar.update');
    Route::post('/admin/profile/avatar/remove', [ProfileController::class, 'removeAvatar'])->name('admin.profile.avatar.remove');
    
    // User Management Routes - requires user permissions
    Route::prefix('admin')->group(function () {
        // Staff management - requires viewAny_staff permission (MUST BE BEFORE /users/{user} route)
        Route::middleware(['permission:viewAny_staff'])->group(function () {
            Route::get('/users/staff', [UserController::class, 'staff'])->name('admin.users.staff');
        });
        
        // Regular users management - requires viewAny_user permission
        Route::middleware(['permission:viewAny_user'])->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
            Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('admin.users.approve');
            Route::post('/users/{user}/disapprove', [UserController::class, 'disapprove'])->name('admin.users.disapprove');
            Route::post('/users/{user}/change-status', [UserController::class, 'changeStatus'])->name('admin.users.change-status');
            Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
            Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
            Route::post('/users/{user}/avatar', [UserController::class, 'updateAvatar'])->name('admin.users.avatar.update');
            Route::delete('/users/{user}/avatar', [UserController::class, 'removeAvatar'])->name('admin.users.avatar.remove');
        });
        
        // Role and Permission Management Routes - requires manage_roles permission
        Route::middleware('permission:manage_roles')->group(function () {
            Route::resource('roles', RoleController::class)->names([
                'index' => 'admin.roles.index',
                'create' => 'admin.roles.create',
                'store' => 'admin.roles.store',
                'show' => 'admin.roles.show',
                'edit' => 'admin.roles.edit',
                'update' => 'admin.roles.update',
                'destroy' => 'admin.roles.destroy',
            ]);
            
            Route::resource('permissions', PermissionController::class)->names([
                'index' => 'admin.permissions.index',
                'create' => 'admin.permissions.create',
                'store' => 'admin.permissions.store',
                'show' => 'admin.permissions.show',
                'edit' => 'admin.permissions.edit',
                'update' => 'admin.permissions.update',
                'destroy' => 'admin.permissions.destroy',
            ]);
        });
            
        // User Group Management Routes - requires user_group permissions
        Route::middleware(['permission:viewAny_user_group'])->group(function () {
            Route::post('user-groups/check-conflicts', [UserGroupController::class, 'checkUserGroups'])->name('admin.user-groups.check-conflicts');
            Route::resource('user-groups', UserGroupController::class)->names([
                'index' => 'admin.user-groups.index',
                'create' => 'admin.user-groups.create',
                'store' => 'admin.user-groups.store',
                'show' => 'admin.user-groups.show',
                'edit' => 'admin.user-groups.edit',
                'update' => 'admin.user-groups.update',
                'destroy' => 'admin.user-groups.destroy',
            ]);
        });
    });
    
    // Firebase Configuration and Notification Routes - requires manage_firebase permission
    Route::middleware(['permission:manage_firebase'])->group(function () {
        Route::get('/admin/firebase/test', [FirebaseController::class, 'testConfiguration'])->name('admin.firebase.test');
        Route::get('/admin/firebase/stats', [FirebaseController::class, 'getStatistics'])->name('admin.firebase.stats');
        Route::get('/admin/firebase/diagnostics', [FirebaseTestController::class, 'runDiagnostics'])->name('admin.firebase.diagnostics');
        Route::post('/admin/firebase/fix-configuration', [FirebaseTestController::class, 'fixConfiguration'])->name('admin.firebase.fix-configuration');
        Route::post('/admin/firebase/test-notification', [FirebaseController::class, 'testNotification'])->name('admin.firebase.test-notification');
        Route::get('/admin/firebase/setup-guide', [FirebaseTestController::class, 'showSetupGuide'])->name('admin.firebase.setup-guide');
    });
    
    // Firebase Notification Sending Routes - requires send_notification permission
    Route::middleware(['permission:send_notification'])->group(function () {
        Route::get('/admin/firebase/notifications', [FirebaseController::class, 'showNotificationForm'])->name('admin.firebase.notifications');
        Route::post('/admin/firebase/notifications/user', [FirebaseController::class, 'sendToUser'])->name('admin.firebase.notifications.user');
        Route::post('/admin/firebase/notifications/group', [FirebaseController::class, 'sendToUserGroup'])->name('admin.firebase.notifications.group');
        Route::post('/admin/firebase/notifications/all', [FirebaseController::class, 'sendToAllUsers'])->name('admin.firebase.notifications.all');
        
        // Admin Scheduled Notifications Management Routes
        Route::get('/admin/firebase/notifications/data', [FirebaseController::class, 'getNotificationsData'])->name('admin.firebase.notifications.data');
        Route::get('/admin/firebase/notifications/scheduled/{id}', [FirebaseController::class, 'getScheduledNotification'])->name('admin.firebase.scheduled.get');
        Route::put('/admin/firebase/notifications/scheduled/{id}', [FirebaseController::class, 'updateScheduledNotification'])->name('admin.firebase.scheduled.update');
        Route::delete('/admin/firebase/notifications/scheduled/{id}', [FirebaseController::class, 'cancelScheduledNotification'])->name('admin.firebase.scheduled.cancel');
    });
    
    // Product Management Routes - uses policy-based authorization
    Route::prefix('admin')->group(function () {
        Route::resource('products', ProductController::class)->names([
            'index' => 'admin.products.index',
            'create' => 'admin.products.create',
            'store' => 'admin.products.store',
            'show' => 'admin.products.show',
            'edit' => 'admin.products.edit',
            'update' => 'admin.products.update',
            'destroy' => 'admin.products.destroy',
        ]);
        
        // Additional product routes
        Route::get('/products/{product}/details', [ProductController::class, 'showDetails'])->name('admin.products.details');
        Route::get('/products-low-stock', [ProductController::class, 'lowStock'])->name('admin.products.low-stock');
        
        // Product Analytics Routes - requires viewAny_product_analytics permission
        Route::middleware(['permission:viewAny_product_analytics'])->group(function () {
            Route::get('/analytics/products', [ProductAnalyticsController::class, 'index'])->name('admin.analytics.products');
            Route::get('/analytics/products/export', [ProductAnalyticsController::class, 'export'])->name('admin.analytics.products.export');
            Route::get('/analytics/products/chart-data', [ProductAnalyticsController::class, 'getChartData'])->name('admin.analytics.products.chart-data');
            Route::get('/analytics/products/{product}', [ProductAnalyticsController::class, 'show'])->name('admin.analytics.products.show');
        });
        
        // Product Attributes Routes - requires attribute permissions
        Route::middleware(['permission:viewAny_attribute'])->group(function () {
            Route::resource('attributes', ProductAttributeController::class)->names([
                'index' => 'admin.attributes.index',
                'create' => 'admin.attributes.create',
                'store' => 'admin.attributes.store',
                'edit' => 'admin.attributes.edit',
                'update' => 'admin.attributes.update',
                'destroy' => 'admin.attributes.destroy',
            ]);
            Route::get('/attributes/get-all', [ProductAttributeController::class, 'getAttributes'])->name('admin.attributes.get-all');
            Route::post('/attributes/{attribute}/values', [ProductAttributeController::class, 'storeValue'])->name('admin.attributes.values.store');
            Route::put('/attributes/{attribute}/values/{value}', [ProductAttributeController::class, 'updateValue'])->name('admin.attributes.values.update');
            Route::delete('/attributes/{attribute}/values/{value}', [ProductAttributeController::class, 'destroyValue'])->name('admin.attributes.values.destroy');
        });
        
    });

    // Category Management Routes - uses policy-based authorization
    Route::prefix('admin')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('admin.categories.show');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
        
        // AJAX routes for product management
        Route::get('/categories-all', [CategoryController::class, 'getAllCategories'])->name('admin.categories.all');
        Route::post('/categories/create', [CategoryController::class, 'createCategory'])->name('admin.categories.create.ajax');
        Route::post('/subcategories/create', [CategoryController::class, 'createSubCategory'])->name('admin.subcategories.create.ajax');
        
        // Subcategory routes
        Route::get('/categories/{category}/subcategories', [CategoryController::class, 'getSubCategories'])->name('admin.categories.subcategories');
        Route::post('/subcategories', [CategoryController::class, 'storeSubCategory'])->name('admin.subcategories.store');
        Route::get('/subcategories/{subCategory}', [CategoryController::class, 'showSubCategory'])->name('admin.subcategories.show');
        Route::put('/subcategories/{subCategory}', [CategoryController::class, 'updateSubCategory'])->name('admin.subcategories.update');
        Route::delete('/subcategories/{subCategory}', [CategoryController::class, 'destroySubCategory'])->name('admin.subcategories.destroy');
    });
    
    // Test route for debugging
    Route::get('/test-media', function () {
        return response()->json(['message' => 'Test route working']);
    });

    // Test route to check categories and subcategories
    Route::get('/test-categories', function () {
        $categories = \App\Models\Category::with('subCategories')->get();
        return response()->json($categories);
    });

    // Proforma Invoice Routes - requires manage_proforma_invoices permission
    Route::prefix('admin')->middleware(['permission:manage_proforma_invoices'])->group(function () {
        Route::get('/proforma-invoice', [ProformaInvoiceController::class, 'index'])->name('admin.proforma-invoice.index');
        Route::get('/proforma-invoice/{id}', [ProformaInvoiceController::class, 'show'])->name('admin.proforma-invoice.show');
        Route::get('/proforma-invoice/{id}/download-pdf', [ProformaInvoiceController::class, 'downloadPDF'])->name('admin.proforma-invoice.download-pdf');
        Route::put('/proforma-invoice/{id}', [ProformaInvoiceController::class, 'update'])->name('admin.proforma-invoice.update');
        Route::put('/proforma-invoice/{id}/update-status', [ProformaInvoiceController::class, 'updateStatus'])->name('admin.proforma-invoice.update-status');
        Route::delete('/proforma-invoice/{id}/remove-item', [ProformaInvoiceController::class, 'removeItem'])->name('admin.proforma-invoice.remove-item');
        Route::delete('/proforma-invoice/{id}', [ProformaInvoiceController::class, 'destroy'])->name('admin.proforma-invoice.destroy');
    });
    
    // Without GST Invoice Routes - requires manage_proforma_invoices permission
    Route::prefix('admin')->middleware(['permission:manage_proforma_invoices'])->group(function () {
        Route::get('/proforma-invoice-black', [WithoutGstInvoiceController::class, 'index'])->name('admin.without-gst-invoice.index');
        Route::get('/proforma-invoice-black/{id}', [WithoutGstInvoiceController::class, 'show'])->name('admin.without-gst-invoice.show');
        Route::get('/proforma-invoice-black/{id}/download-pdf', [WithoutGstInvoiceController::class, 'downloadPDF'])->name('admin.without-gst-invoice.download-pdf');
        Route::put('/proforma-invoice-black/{id}', [WithoutGstInvoiceController::class, 'update'])->name('admin.without-gst-invoice.update');
        Route::put('/proforma-invoice-black/{id}/update-status', [WithoutGstInvoiceController::class, 'updateStatus'])->name('admin.without-gst-invoice.update-status');
        Route::delete('/proforma-invoice-black/{id}/remove-item', [WithoutGstInvoiceController::class, 'removeItem'])->name('admin.without-gst-invoice.remove-item');
        Route::delete('/proforma-invoice-black/{id}', [WithoutGstInvoiceController::class, 'destroy'])->name('admin.without-gst-invoice.destroy');
    });

    // Pending Bills Routes - requires manage_pending_bills permission
    Route::prefix('admin')->middleware(['permission:manage_pending_bills'])->group(function () {
        Route::get('/pending-bills', [\App\Http\Controllers\Admin\PendingBillController::class, 'index'])->name('admin.pending-bills.index');
        Route::get('/pending-bills/user-summary', [\App\Http\Controllers\Admin\PendingBillController::class, 'userSummary'])->name('admin.pending-bills.user-summary');
        Route::get('/pending-bills/user/{userId}', [\App\Http\Controllers\Admin\PendingBillController::class, 'userBills'])->name('admin.pending-bills.user');
        Route::post('/pending-bills/{id}/update-payment', [\App\Http\Controllers\Admin\PendingBillController::class, 'updatePayment'])->name('admin.pending-bills.update-payment');
        Route::post('/pending-bills/{id}/add-payment', [\App\Http\Controllers\Admin\PendingBillController::class, 'addPayment'])->name('admin.pending-bills.add-payment');
    });
    
    // Pages Routes - requires page permissions
    Route::prefix('admin')->middleware(['permission:viewAny_page'])->group(function () {
        Route::resource('pages', PageController::class)->names([
            'index' => 'admin.pages.index',
            'create' => 'admin.pages.create',
            'store' => 'admin.pages.store',
            'show' => 'admin.pages.show',
            'edit' => 'admin.pages.edit',
            'update' => 'admin.pages.update',
            'destroy' => 'admin.pages.destroy',
        ]);
    });

    // Notification Routes - accessible to all admin users (own notifications)
    Route::prefix('admin')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-as-read');
        Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-as-read');
        Route::post('/notifications/invoice/{invoiceId}/mark-as-read', [NotificationController::class, 'markInvoiceNotificationsAsRead'])->name('admin.notifications.invoice.mark-as-read');
        Route::get('/notifications/data', [NotificationController::class, 'getUserNotifications'])->name('admin.notifications.data');
    });

    // Lead Management Routes - requires viewAny_lead permission
    Route::prefix('admin')->middleware(['permission:viewAny_lead'])->group(function () {
        Route::resource('leads', LeadController::class)->names([
            'index' => 'admin.leads.index',
            'create' => 'admin.leads.create',
            'store' => 'admin.leads.store',
            'show' => 'admin.leads.show',
            'edit' => 'admin.leads.edit',
            'update' => 'admin.leads.update',
            'destroy' => 'admin.leads.destroy',
        ]);
        
        // Trashed leads routes
        Route::get('/leads-trashed', [LeadController::class, 'trashed'])->name('admin.leads.trashed');
        Route::post('/leads/{id}/restore', [LeadController::class, 'restore'])->name('admin.leads.restore');
        Route::delete('/leads/{id}/force-delete', [LeadController::class, 'forceDelete'])->name('admin.leads.force-delete');
    });

    // Coupon Management Routes - requires viewAny_coupon permission
    Route::prefix('admin')->middleware(['permission:viewAny_coupon'])->group(function () {
        Route::resource('coupons', CouponController::class)->names([
            'index' => 'admin.coupons.index',
            'create' => 'admin.coupons.create',
            'store' => 'admin.coupons.store',
            'show' => 'admin.coupons.show',
            'edit' => 'admin.coupons.edit',
            'update' => 'admin.coupons.update',
            'destroy' => 'admin.coupons.destroy',
        ]);
        Route::post('/coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('admin.coupons.toggle-status');
    });

    // Attendance Management Routes - requires viewAny_attendance permission
    Route::prefix('admin')->middleware(['permission:viewAny_attendance'])->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/attendance/bulk', [AttendanceController::class, 'bulk'])->name('admin.attendance.bulk');
        Route::post('/attendance/bulk', [AttendanceController::class, 'storeBulk'])->name('admin.attendance.store-bulk');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('admin.attendance.store');
        Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('admin.attendance.report');
        Route::get('/attendance/data', [AttendanceController::class, 'getAttendance'])->name('admin.attendance.data');
        Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy'])->name('admin.attendance.destroy');
    });

    // Salary Management Routes - requires viewAny_salary permission
    Route::prefix('admin')->middleware(['permission:viewAny_salary'])->group(function () {
        Route::get('/salary', [SalaryController::class, 'index'])->name('admin.salary.index');
        Route::get('/salary/create', [SalaryController::class, 'create'])->name('admin.salary.create');
        Route::post('/salary', [SalaryController::class, 'store'])->name('admin.salary.store');
        Route::get('/salary/user/{userId}', [SalaryController::class, 'show'])->name('admin.salary.show');
        Route::delete('/salary/{id}', [SalaryController::class, 'destroy'])->name('admin.salary.destroy');
        
        // Salary Payments/Payroll
        Route::get('/salary/payments', [SalaryController::class, 'payments'])->name('admin.salary.payments');
        Route::post('/salary/payments/{id}/process', [SalaryController::class, 'processPayment'])->name('admin.salary.process-payment');
        Route::post('/salary/payments/{id}/adjustments', [SalaryController::class, 'updateAdjustments'])->name('admin.salary.update-adjustments');
        Route::post('/salary/payments/{id}/recalculate', [SalaryController::class, 'recalculate'])->name('admin.salary.recalculate');
        Route::get('/salary/slip/{id}', [SalaryController::class, 'slip'])->name('admin.salary.slip');
        Route::get('/salary/slip/{id}/download', [SalaryController::class, 'downloadSlip'])->name('admin.salary.download-slip');
    });

    // Vendor Management Routes - requires viewAny_vendor permission
    Route::prefix('admin')->middleware(['permission:viewAny_vendor'])->group(function () {
        Route::get('/vendors', [VendorController::class, 'index'])->name('admin.vendors.index');
        Route::get('/vendors/create', [VendorController::class, 'create'])->name('admin.vendors.create');
        Route::post('/vendors', [VendorController::class, 'store'])->name('admin.vendors.store');
        Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->name('admin.vendors.show');
        Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('admin.vendors.edit');
        Route::put('/vendors/{vendor}', [VendorController::class, 'update'])->name('admin.vendors.update');
        Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->name('admin.vendors.destroy');
        
        // Vendor status management
        Route::post('/vendors/{vendor}/approve', [VendorController::class, 'approve'])->name('admin.vendors.approve');
        Route::post('/vendors/{vendor}/reject', [VendorController::class, 'reject'])->name('admin.vendors.reject');
        Route::post('/vendors/{vendor}/suspend', [VendorController::class, 'suspend'])->name('admin.vendors.suspend');
        Route::post('/vendors/{vendor}/reactivate', [VendorController::class, 'reactivate'])->name('admin.vendors.reactivate');
        
        // Vendor commission management
        Route::post('/vendors/{vendor}/commission', [VendorController::class, 'updateCommission'])->name('admin.vendors.commission');
        
        // Vendor permissions management
        Route::get('/vendors/{vendor}/permissions', [VendorController::class, 'permissions'])->name('admin.vendors.permissions');
        Route::post('/vendors/{vendor}/permissions', [VendorController::class, 'updatePermissions'])->name('admin.vendors.permissions.update');
    });

    // Vendor Payment Management Routes - requires viewAny_vendor permission
    Route::prefix('admin')->middleware(['permission:viewAny_vendor'])->group(function () {
        Route::get('/vendor-payments', [VendorPaymentController::class, 'index'])->name('admin.vendor-payments.index');
        
        // Earnings report routes (must be before {vendor} route)
        Route::get('/vendor-payments/earnings-report', [VendorPaymentController::class, 'earningsReport'])->name('admin.vendor-payments.earnings-report');
        Route::get('/vendor-payments/earnings-report/export', [VendorPaymentController::class, 'exportEarningsReport'])->name('admin.vendor-payments.earnings-report.export');
        
        // Other specific routes (before {vendor} route)
        Route::get('/vendor-payments/balance/check', [VendorPaymentController::class, 'getBalance'])->name('admin.vendor-payments.balance');
        Route::get('/vendor-payments/sync-all-payouts', [VendorPaymentController::class, 'syncAllPendingPayouts'])->name('admin.vendor-payments.sync-all-payouts');
        Route::get('/vendor-payments/refresh-balance', [VendorPaymentController::class, 'refreshRazorpayXBalance'])->name('admin.vendor-payments.refresh-balance');
        Route::get('/vendor-payments/payout/{payout}/details', [VendorPaymentController::class, 'getPayoutDetails'])->name('admin.vendor-payments.payout-details');
        Route::post('/vendor-payments/payout/{payout}/retry', [VendorPaymentController::class, 'retryPayout'])->name('admin.vendor-payments.retry-payout');
        Route::post('/vendor-payments/payout/{payout}/sync', [VendorPaymentController::class, 'syncPayoutStatus'])->name('admin.vendor-payments.sync-payout');
        Route::post('/vendor-payments/bulk-payout', [VendorPaymentController::class, 'processBulkPayout'])->name('admin.vendor-payments.bulk-payout');
        Route::post('/vendor-payments/schedule', [VendorPaymentController::class, 'savePayoutSchedule'])->name('admin.vendor-payments.schedule');
        
        // Vendor-specific routes (must be last)
        Route::get('/vendor-payments/{vendor}', [VendorPaymentController::class, 'show'])->name('admin.vendor-payments.show');
        Route::post('/vendor-payments/{vendor}/payout', [VendorPaymentController::class, 'initiatePayout'])->name('admin.vendor-payments.payout');
        Route::post('/vendor-payments/{vendor}/setup-bank', [VendorPaymentController::class, 'setupBankAccount'])->name('admin.vendor-payments.setup-bank');
        Route::post('/vendor-payments/{vendor}/wallet-status', [VendorPaymentController::class, 'updateWalletStatus'])->name('admin.vendor-payments.wallet-status');
        Route::post('/vendor-payments/{vendor}/bank-account', [VendorPaymentController::class, 'updateBankAccount'])->name('admin.vendor-payments.bank-account');
    });

    // Referral Management Routes - requires viewAny_referral permission
    Route::prefix('admin')->middleware(['permission:viewAny_referral'])->group(function () {
        Route::get('/referrals', [ReferralController::class, 'index'])->name('admin.referrals.index');
        Route::get('/referrals/create', [ReferralController::class, 'create'])->name('admin.referrals.create');
        Route::post('/referrals', [ReferralController::class, 'store'])->name('admin.referrals.store');
        Route::get('/referrals/{referral}/edit', [ReferralController::class, 'edit'])->name('admin.referrals.edit');
        Route::put('/referrals/{referral}', [ReferralController::class, 'update'])->name('admin.referrals.update');
        Route::delete('/referrals/{referral}', [ReferralController::class, 'destroy'])->name('admin.referrals.destroy');
        Route::post('/referrals/{referral}/update-status', [ReferralController::class, 'updateStatus'])->name('admin.referrals.update-status');
        Route::post('/referrals/{referral}/update-payment-status', [ReferralController::class, 'updatePaymentStatus'])->name('admin.referrals.update-payment-status');
        
        // Referral Users Routes
        Route::get('/referrals/{referral}/users', [ReferralController::class, 'users'])->name('admin.referrals.users');
        Route::post('/referrals/{referral}/users', [ReferralController::class, 'storeUser'])->name('admin.referrals.users.store');
        Route::delete('/referrals/{referral}/users/{referralUser}', [ReferralController::class, 'destroyUser'])->name('admin.referrals.users.destroy');
        
        // Referral Payment Routes
        Route::post('/referrals/{referral}/users/{referralUser}/mark-paid', [ReferralController::class, 'markUserPaid'])->name('admin.referrals.users.mark-paid');
        Route::post('/referrals/{referral}/users/mark-multiple-paid', [ReferralController::class, 'markMultiplePaid'])->name('admin.referrals.users.mark-multiple-paid');
    });

    // Referral Earnings Routes (Vendor Referral Commissions) - requires viewAny_referral_earning permission
    Route::prefix('admin')->middleware(['permission:viewAny_referral_earning'])->group(function () {
        Route::get('/referral-earnings', [\App\Http\Controllers\Admin\ReferralEarningController::class, 'index'])->name('admin.referral-earnings.index');
        Route::get('/referral-earnings/{earning}', [\App\Http\Controllers\Admin\ReferralEarningController::class, 'show'])->name('admin.referral-earnings.show')->middleware('permission:view_referral_earning');
        Route::post('/referral-earnings/{earning}/approve', [\App\Http\Controllers\Admin\ReferralEarningController::class, 'approve'])->name('admin.referral-earnings.approve')->middleware('permission:approve_referral_earning');
        Route::post('/referral-earnings/bulk-approve', [\App\Http\Controllers\Admin\ReferralEarningController::class, 'bulkApprove'])->name('admin.referral-earnings.bulk-approve')->middleware('permission:approve_referral_earning');
        Route::post('/referral-earnings/create-payout', [\App\Http\Controllers\Admin\ReferralEarningController::class, 'createPayout'])->name('admin.referral-earnings.create-payout')->middleware('permission:create_referral_payout');
        Route::get('/referral-earnings/by-vendor/{vendor}', [\App\Http\Controllers\Admin\ReferralEarningController::class, 'byVendor'])->name('admin.referral-earnings.by-vendor');
    });

    // Activity Logs Routes - requires viewAny_activity_log permission
    Route::prefix('admin')->middleware(['permission:viewAny_activity_log'])->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
        Route::get('/activity-logs/data', [ActivityLogController::class, 'getData'])->name('admin.activity-logs.data');
        Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('admin.activity-logs.export');
        Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('admin.activity-logs.show');
        Route::post('/activity-logs/clear', [ActivityLogController::class, 'clear'])->name('admin.activity-logs.clear');
    });

    // Feature Settings Routes - requires manage_feature_settings permission
    Route::prefix('admin/feature-settings')->middleware(['permission:manage_feature_settings'])->group(function () {
        Route::get('/', [FeatureSettingsController::class, 'index'])->name('admin.feature-settings.index');
        Route::post('/', [FeatureSettingsController::class, 'update'])->name('admin.feature-settings.update');
        Route::post('/{featureKey}/toggle', [FeatureSettingsController::class, 'toggle'])->name('admin.feature-settings.toggle');
    });

    // Task Management Routes (requires manage_tasks permission)
    Route::prefix('admin/tasks')->middleware(['permission:manage_tasks'])->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('admin.tasks.index');
        Route::get('/create', [TaskController::class, 'create'])->name('admin.tasks.create');
        Route::post('/', [TaskController::class, 'store'])->name('admin.tasks.store');
        Route::get('/statistics', [TaskController::class, 'statistics'])->name('admin.tasks.statistics');
        Route::get('/{id}', [TaskController::class, 'show'])->name('admin.tasks.show');
        Route::get('/{id}/edit', [TaskController::class, 'edit'])->name('admin.tasks.edit');
        Route::put('/{id}', [TaskController::class, 'update'])->name('admin.tasks.update');
        Route::delete('/{id}', [TaskController::class, 'destroy'])->name('admin.tasks.destroy');
        Route::post('/{id}/comment', [TaskController::class, 'addComment'])->name('admin.tasks.comment');
        Route::post('/{id}/status', [TaskController::class, 'updateStatus'])->name('admin.tasks.status');
    });

});
