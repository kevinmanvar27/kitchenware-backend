<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\Auth\RegisterController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\CategoryController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\ReportController;
use App\Http\Controllers\Vendor\LeadController;
use App\Http\Controllers\Vendor\CouponController;
use App\Http\Controllers\Vendor\ProductAnalyticsController;
use App\Http\Controllers\Vendor\SalaryController;
use App\Http\Controllers\Vendor\AttendanceController;
use App\Http\Controllers\Vendor\PendingBillController;
use App\Http\Controllers\Vendor\InvoiceController;
use App\Http\Controllers\Vendor\WithoutGstInvoiceController;
use App\Http\Controllers\Vendor\CustomerController;
use App\Http\Controllers\Vendor\StaffController;
use App\Http\Controllers\Vendor\NotificationController;
use App\Http\Controllers\Vendor\PushNotificationController;
use App\Http\Controllers\Vendor\ActivityLogController;
use App\Http\Controllers\Vendor\FeatureSettingsController;
use App\Http\Controllers\Vendor\TaskController;
use App\Http\Controllers\Vendor\BannerController;
use App\Http\Controllers\Vendor\SubscriptionController;
use App\Http\Controllers\Vendor\VendorReferralController;

/*
|--------------------------------------------------------------------------
| Vendor Routes
|--------------------------------------------------------------------------
|
| Here is where you can register vendor routes for your application.
| All users (vendors, staff, admin, customers) now use the unified login at /login
|
*/

// Vendor Authentication Routes (Guest)
Route::prefix('vendor')->name('vendor.')->group(function () {
    // Redirect old login routes to unified login
    Route::get('login', function () {
        return redirect()->route('login');
    })->name('login');
    
    Route::get('staff/login', function () {
        return redirect()->route('login');
    })->name('staff.login');
    
    // Registration routes (only for new vendors, not staff)
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.post');
    
    // Logout route - handle directly instead of redirecting
    Route::post('logout', function () {
        $user = auth()->user();
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    })->name('logout');
    
    // Status pages (accessible when logged in but not approved)
    Route::middleware('auth')->group(function () {
        Route::get('pending', [DashboardController::class, 'pending'])->name('pending');
        Route::get('rejected', [DashboardController::class, 'rejected'])->name('rejected');
        Route::get('suspended', [DashboardController::class, 'suspended'])->name('suspended');
        
        // Subscription routes (accessible without active subscription)
        Route::get('subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
        Route::get('subscription/current', [SubscriptionController::class, 'current'])->name('subscription.current');
        Route::get('subscription/history', [SubscriptionController::class, 'history'])->name('subscription.history');
        Route::post('subscription/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
        Route::post('subscription/verify-payment', [SubscriptionController::class, 'verifyPayment'])->name('subscription.verify');
        Route::post('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
        
        // Referral Code (accessible without active subscription - vendor owners only)
        Route::middleware('vendor.owner')->group(function () {
            Route::get('referral/my-code', [VendorReferralController::class, 'myCode'])->name('referral.my-code');
            Route::get('referral/earnings', [VendorReferralController::class, 'earnings'])->name('referral.earnings');
        });
    });
});

// Vendor Protected Routes (Requires authentication and approved vendor)
Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'vendor', 'vendor.subscription'])->group(function () {
    
    // Dashboard - requires dashboard permission
    Route::middleware('vendor.permission:dashboard')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    });
    
    // Profile Management - requires profile permission
    Route::middleware('vendor.permission:profile')->group(function () {
        Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/address', [ProfileController::class, 'updateAddress'])->name('profile.update-address');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
        Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
        Route::post('profile/avatar/remove', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
        Route::get('profile/bank-details', [ProfileController::class, 'showBankDetails'])->name('profile.bank-details');
    });
    
    // Store Settings - requires store_settings permission
    Route::middleware('vendor.permission:store_settings')->group(function () {
        Route::put('profile/store-settings', [ProfileController::class, 'updateStoreSettings'])->name('profile.store-settings');
        Route::get('profile/store', [ProfileController::class, 'storeSettings'])->name('profile.store');
        Route::post('profile/store-logo', [ProfileController::class, 'updateStoreLogo'])->name('profile.store-logo.update');
        Route::post('profile/store-logo/remove', [ProfileController::class, 'removeStoreLogo'])->name('profile.store-logo.remove');
        Route::post('profile/store-banner', [ProfileController::class, 'updateStoreBanner'])->name('profile.store-banner.update');
        Route::post('profile/store-banner/remove', [ProfileController::class, 'removeStoreBanner'])->name('profile.store-banner.remove');
        Route::post('profile/bank-details', [ProfileController::class, 'updateBankDetails'])->name('profile.bank-details.update');
        Route::post('profile/social-links', [ProfileController::class, 'updateSocialLinks'])->name('profile.social-links.update');
    });
    
    // Product Management - requires products permission
    Route::middleware('vendor.permission:products')->group(function () {
        Route::resource('products', ProductController::class);
        Route::get('products-low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
        Route::post('products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    });
    
    // Product Attributes - requires attributes permission (full CRUD)
    Route::middleware('vendor.permission:attributes')->group(function () {
        Route::get('attributes', [\App\Http\Controllers\Vendor\AttributeController::class, 'index'])->name('attributes.index');
        Route::get('attributes/all', [\App\Http\Controllers\Vendor\AttributeController::class, 'getAll'])->name('attributes.all');
        Route::get('attributes/create', [\App\Http\Controllers\Vendor\AttributeController::class, 'create'])->name('attributes.create');
        Route::post('attributes', [\App\Http\Controllers\Vendor\AttributeController::class, 'store'])->name('attributes.store');
        Route::get('attributes/{attribute}', [\App\Http\Controllers\Vendor\AttributeController::class, 'show'])->name('attributes.show');
        Route::get('attributes/{attribute}/edit', [\App\Http\Controllers\Vendor\AttributeController::class, 'edit'])->name('attributes.edit');
        Route::put('attributes/{attribute}', [\App\Http\Controllers\Vendor\AttributeController::class, 'update'])->name('attributes.update');
        Route::delete('attributes/{attribute}', [\App\Http\Controllers\Vendor\AttributeController::class, 'destroy'])->name('attributes.destroy');
        
        // Attribute values AJAX routes
        Route::post('attributes/{attribute}/values', [\App\Http\Controllers\Vendor\AttributeController::class, 'storeValue'])->name('attributes.values.store');
        Route::put('attributes/{attribute}/values/{value}', [\App\Http\Controllers\Vendor\AttributeController::class, 'updateValue'])->name('attributes.values.update');
        Route::delete('attributes/{attribute}/values/{value}', [\App\Http\Controllers\Vendor\AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');
    });
    
    // Category Management - requires categories permission
    Route::middleware('vendor.permission:categories')->group(function () {
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        
        // AJAX routes for categories
        Route::get('categories-all', [CategoryController::class, 'getAllCategories'])->name('categories.all');
        Route::post('categories/create', [CategoryController::class, 'createCategory'])->name('categories.create.ajax');
        Route::post('subcategories/create', [CategoryController::class, 'createSubCategory'])->name('subcategories.create.ajax');
        
        // Subcategory routes
        Route::get('categories/{category}/subcategories', [CategoryController::class, 'getSubCategories'])->name('categories.subcategories');
        Route::post('subcategories', [CategoryController::class, 'storeSubCategory'])->name('subcategories.store');
        Route::get('subcategories/{subCategory}', [CategoryController::class, 'showSubCategory'])->name('subcategories.show');
        Route::put('subcategories/{subCategory}', [CategoryController::class, 'updateSubCategory'])->name('subcategories.update');
        Route::delete('subcategories/{subCategory}', [CategoryController::class, 'destroySubCategory'])->name('subcategories.destroy');
    });
    
    // Notification Management - always accessible
    Route::post('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    
    // Reports & Analytics - requires reports permission
    Route::middleware('vendor.permission:reports')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
    
    // Lead Management - requires leads permission
    Route::middleware('vendor.permission:leads')->group(function () {
        Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
        Route::get('leads/trashed', [LeadController::class, 'trashed'])->name('leads.trashed');
        Route::get('leads/reminders', [LeadController::class, 'reminders'])->name('leads.reminders');
        Route::get('leads/reminders/due', [LeadController::class, 'dueReminders'])->name('leads.reminders.due');
        Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
        Route::post('leads/{id}/restore', [LeadController::class, 'restore'])->name('leads.restore');
        Route::delete('leads/{id}/force-delete', [LeadController::class, 'forceDelete'])->name('leads.force-delete');
        
        // Lead Reminder Management
        Route::post('leads/{lead}/reminders', [LeadController::class, 'storeReminder'])->name('leads.reminders.store');
        Route::put('leads/reminders/{reminder}', [LeadController::class, 'updateReminder'])->name('leads.reminders.update');
        Route::post('leads/reminders/{reminder}/complete', [LeadController::class, 'completeReminder'])->name('leads.reminders.complete');
        Route::post('leads/reminders/{reminder}/dismiss', [LeadController::class, 'dismissReminder'])->name('leads.reminders.dismiss');
        Route::delete('leads/reminders/{reminder}', [LeadController::class, 'destroyReminder'])->name('leads.reminders.destroy');
    });
    
    // Coupon Management - requires coupons permission
    Route::middleware('vendor.permission:coupons')->group(function () {
        Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::get('coupons/create', [CouponController::class, 'create'])->name('coupons.create');
        Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::get('coupons/{coupon}', [CouponController::class, 'show'])->name('coupons.show');
        Route::get('coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
        Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
        Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
        Route::post('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
    });
    
    // Product Analytics - requires analytics permission
    Route::middleware('vendor.permission:analytics')->group(function () {
        Route::get('analytics/products', [ProductAnalyticsController::class, 'index'])->name('analytics.products');
        Route::get('analytics/products/export', [ProductAnalyticsController::class, 'export'])->name('analytics.products.export');
        Route::get('analytics/products/{product}', [ProductAnalyticsController::class, 'show'])->name('analytics.products.show');
    });
    
    // Push Notifications - requires push_notifications permission
    Route::middleware('vendor.permission:push_notifications')->group(function () {
        Route::get('push-notifications', [PushNotificationController::class, 'index'])->name('push-notifications.index');
        Route::post('push-notifications/send-all', [PushNotificationController::class, 'sendToAll'])->name('push-notifications.send-all');
        Route::post('push-notifications/send-to-customers', [PushNotificationController::class, 'sendToCustomers'])->name('push-notifications.send-to-customers');
        Route::get('push-notifications/customers', [PushNotificationController::class, 'getCustomers'])->name('push-notifications.customers');
        Route::get('push-notifications/firebase-status', [PushNotificationController::class, 'checkFirebaseStatus'])->name('push-notifications.firebase-status');
        
        // Scheduled notifications management
        Route::get('push-notifications/data', [PushNotificationController::class, 'getNotificationsData'])->name('push-notifications.data');
        Route::get('push-notifications/scheduled/{id}', [PushNotificationController::class, 'getScheduledNotification'])->name('push-notifications.scheduled.get');
        Route::put('push-notifications/scheduled/{id}', [PushNotificationController::class, 'updateScheduledNotification'])->name('push-notifications.scheduled.update');
        Route::post('push-notifications/scheduled/{id}/cancel', [PushNotificationController::class, 'cancelScheduledNotification'])->name('push-notifications.scheduled.cancel');
        Route::delete('push-notifications/scheduled/{id}', [PushNotificationController::class, 'deleteScheduledNotification'])->name('push-notifications.scheduled.delete');
    });
    
    // Salary Management - requires salary permission
    Route::middleware('vendor.permission:salary')->group(function () {
        Route::get('salary', [SalaryController::class, 'index'])->name('salary.index');
        Route::get('salary/create', [SalaryController::class, 'create'])->name('salary.create');
        Route::post('salary', [SalaryController::class, 'store'])->name('salary.store');
        Route::get('salary/payments', [SalaryController::class, 'payments'])->name('salary.payments');
        Route::get('salary/{userId}', [SalaryController::class, 'show'])->name('salary.show');
        Route::delete('salary/{id}', [SalaryController::class, 'destroy'])->name('salary.destroy');
        Route::post('salary/payments/{id}/process', [SalaryController::class, 'processPayment'])->name('salary.payments.process');
        Route::put('salary/payments/{id}/adjustments', [SalaryController::class, 'updateAdjustments'])->name('salary.payments.adjustments');
        Route::get('salary/payments/{id}/slip', [SalaryController::class, 'slip'])->name('salary.payments.slip');
    });
    
    // Attendance Management - requires attendance permission
    Route::middleware('vendor.permission:attendance')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/bulk', [AttendanceController::class, 'bulk'])->name('attendance.bulk');
        Route::post('attendance/bulk', [AttendanceController::class, 'storeBulk'])->name('attendance.store-bulk');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/data', [AttendanceController::class, 'getAttendance'])->name('attendance.data');
        Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        Route::delete('attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    });
    
    // Pending Bills Management - requires pending_bills permission
    Route::middleware('vendor.permission:pending_bills')->group(function () {
        Route::get('pending-bills', [PendingBillController::class, 'index'])->name('pending-bills.index');
        Route::get('pending-bills/summary', [PendingBillController::class, 'summary'])->name('pending-bills.summary');
        Route::get('pending-bills/user/{userId}', [PendingBillController::class, 'userBills'])->name('pending-bills.user');
        Route::get('pending-bills/{invoice}', [PendingBillController::class, 'show'])->name('pending-bills.show');
        Route::post('pending-bills/{invoice}/payment', [PendingBillController::class, 'recordPayment'])->name('pending-bills.record-payment');
        Route::post('pending-bills/{invoice}/add-payment', [PendingBillController::class, 'addPayment'])->name('pending-bills.add-payment');
    });
    
    // Invoices Management - requires invoices permission
    Route::middleware('vendor.permission:invoices')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
        Route::post('invoices/{invoice}/update-status', [InvoiceController::class, 'updateStatus'])->name('invoices.update-status');
        Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::delete('invoices/{invoice}/remove-item', [InvoiceController::class, 'removeItem'])->name('invoices.remove-item');
    });
    
    // Without GST Invoices Management - requires invoices permission
    Route::middleware('vendor.permission:invoices')->group(function () {
        Route::get('invoices-black', [WithoutGstInvoiceController::class, 'index'])->name('invoices-black.index');
        Route::get('invoices-black/{id}', [WithoutGstInvoiceController::class, 'show'])->name('invoices-black.show');
        Route::get('invoices-black/{id}/download-pdf', [WithoutGstInvoiceController::class, 'downloadPDF'])->name('invoices-black.download-pdf');
        Route::put('invoices-black/{id}', [WithoutGstInvoiceController::class, 'update'])->name('invoices-black.update');
        Route::put('invoices-black/{id}/update-status', [WithoutGstInvoiceController::class, 'updateStatus'])->name('invoices-black.update-status');
        Route::delete('invoices-black/{id}/remove-item', [WithoutGstInvoiceController::class, 'removeItem'])->name('invoices-black.remove-item');
        Route::delete('invoices-black/{id}', [WithoutGstInvoiceController::class, 'destroy'])->name('invoices-black.destroy');
        Route::post('invoices-black/{id}/add-payment', [WithoutGstInvoiceController::class, 'addPayment'])->name('invoices-black.add-payment');
    });
    
    // Customer Management - requires customers permission
    Route::middleware('vendor.permission:customers')->group(function () {
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
        Route::post('customers/{customer}/reset-password', [CustomerController::class, 'resetPassword'])->name('customers.reset-password');
        Route::post('customers/{customer}/upload-avatar', [CustomerController::class, 'uploadAvatar'])->name('customers.upload-avatar');
        Route::delete('customers/{customer}/remove-avatar', [CustomerController::class, 'removeAvatar'])->name('customers.remove-avatar');
    });
    
    // Staff Management - requires staff permission
    Route::middleware('vendor.permission:staff')->group(function () {
        Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('staff/{id}', [StaffController::class, 'show'])->name('staff.show');
        Route::get('staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('staff/{id}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
        Route::post('staff/{id}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
    });
    
    // Activity Logs - requires activity_logs permission
    Route::middleware('vendor.permission:activity_logs')->group(function () {
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('activity-logs/data', [ActivityLogController::class, 'getData'])->name('activity-logs.data');
        Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    });
    
    // Feature Settings - allows vendors to enable/disable features they have override permission for
    Route::get('feature-settings', [FeatureSettingsController::class, 'index'])->name('feature-settings.index');
    Route::post('feature-settings', [FeatureSettingsController::class, 'update'])->name('feature-settings.update');
    Route::post('feature-settings/{featureKey}/toggle', [FeatureSettingsController::class, 'toggle'])->name('feature-settings.toggle');
    
    // Task Management - requires view_tasks permission
    Route::middleware('vendor.permission:view_tasks')->group(function () {
        Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/statistics', [TaskController::class, 'statistics'])->name('tasks.statistics');
        Route::get('tasks/{id}', [TaskController::class, 'show'])->name('tasks.show');
        Route::post('tasks/{id}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
        Route::post('tasks/{id}/comment', [TaskController::class, 'addComment'])->name('tasks.comment');
        Route::post('tasks/{id}/verify', [TaskController::class, 'verify'])->name('tasks.verify');
    });
    
    // Banner Management - requires banners permission
    Route::middleware('vendor.permission:banners')->group(function () {
        Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
        Route::get('banners/create', [BannerController::class, 'create'])->name('banners.create');
        Route::post('banners', [BannerController::class, 'store'])->name('banners.store');
        Route::post('banners/reorder', [BannerController::class, 'reorder'])->name('banners.reorder');
        Route::get('banners/{id}/edit', [BannerController::class, 'edit'])->name('banners.edit');
        Route::put('banners/{id}', [BannerController::class, 'update'])->name('banners.update');
        Route::delete('banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
        Route::patch('banners/{id}/toggle-status', [BannerController::class, 'toggleStatus'])->name('banners.toggle-status');
        
        // API endpoints for category and product selection
        Route::get('banners/api/categories', [BannerController::class, 'getCategories'])->name('banners.api.categories');
        Route::get('banners/api/products', [BannerController::class, 'getProducts'])->name('banners.api.products');
    });
});

?>
