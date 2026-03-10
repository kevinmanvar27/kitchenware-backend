<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProformaInvoice;
use App\Models\WithoutGstInvoice;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Define permission checks
        $permissions = $this->getUserPermissions($user);
        
        // Initialize all variables with default values
        $data = $this->getDefaultData();
        
        // Only fetch data based on user permissions
        if ($permissions['canViewUsers']) {
            $data['userCount'] = User::where('user_role', 'user')->count();
            $data['userGroupCount'] = UserGroup::count();
        }
        
        if ($permissions['canViewProducts']) {
            $data['productCount'] = Product::count();
            $data['categoryCount'] = Category::count();
            
            // Low stock products
            $data['lowStockProducts'] = Product::where('in_stock', true)
                ->whereColumn('stock_quantity', '<=', 'low_quantity_threshold')
                ->where('stock_quantity', '>', 0)
                ->orderBy('stock_quantity', 'asc')
                ->take(5)
                ->get();
            
            // Out of stock products count
            $data['outOfStockCount'] = Product::where(function($query) {
                $query->where('in_stock', false)
                      ->orWhere('stock_quantity', '<=', 0);
            })->count();
            
            // Top selling products (only if can view orders too)
            if ($permissions['canViewOrders']) {
                $data['topProducts'] = $this->getTopSellingProducts();
            }
        }
        
        if ($permissions['canViewOrders']) {
            // Revenue statistics
            $data['totalRevenue'] = ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)->sum('total_amount');
            $data['monthlyRevenue'] = ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount');
            
            $lastMonthRevenue = ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->whereYear('created_at', Carbon::now()->subMonth()->year)
                ->sum('total_amount');
            
            $data['revenueGrowth'] = $lastMonthRevenue > 0 
                ? round((($data['monthlyRevenue'] - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) 
                : ($data['monthlyRevenue'] > 0 ? 100 : 0);

            // Order statistics
            $data['totalOrders'] = ProformaInvoice::count();
            $data['pendingOrders'] = ProformaInvoice::whereIn('status', [
                ProformaInvoice::STATUS_DRAFT, 
                ProformaInvoice::STATUS_APPROVED,
                ProformaInvoice::STATUS_DISPATCH,
                ProformaInvoice::STATUS_OUT_FOR_DELIVERY
            ])->count();
            $data['deliveredOrders'] = ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)->count();
            
            // Today's statistics
            $data['todayOrders'] = ProformaInvoice::whereDate('created_at', Carbon::today())->count();
            $data['todayRevenue'] = ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)
                ->whereDate('created_at', Carbon::today())
                ->sum('total_amount');

            // Chart data
            $data['monthlyRevenueData'] = $this->getMonthlyRevenueData();
            $data['weeklyRevenueData'] = $this->getWeeklyRevenueData();
            $data['orderStatusData'] = $this->getOrderStatusDistribution();

            // Recent orders
            $data['recentOrders'] = ProformaInvoice::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
        
        if ($permissions['canViewLeads']) {
            // Lead statistics
            $data['leadStats'] = [
                'total' => Lead::count(),
                'new' => Lead::where('status', 'new')->count(),
                'contacted' => Lead::where('status', 'contacted')->count(),
                'followup' => Lead::where('status', 'followup')->count(),
                'qualified' => Lead::where('status', 'qualified')->count(),
                'converted' => Lead::where('status', 'converted')->count(),
                'lost' => Lead::where('status', 'lost')->count(),
            ];
        }
        
        if ($permissions['canViewPendingBills']) {
            // Pending payments
            $data['pendingPayments'] = ProformaInvoice::where('payment_status', 'pending')
                ->orWhere('payment_status', 'partial')
                ->sum(DB::raw('total_amount - paid_amount'));
        }

        return view('admin.dashboard.index', $data);
    }

    /**
     * Get user permissions for dashboard sections
     *
     * @param User $user
     * @return array
     */
    private function getUserPermissions($user)
    {
        return [
            'canViewProducts' => $user->hasPermission('viewAny_product') || 
                                 $user->hasPermission('create_product') || 
                                 $user->hasPermission('update_product') || 
                                 $user->hasPermission('delete_product'),
            'canCreateProduct' => $user->hasPermission('create_product'),
            'canViewOrders' => $user->hasPermission('manage_proforma_invoices'),
            'canViewPendingBills' => $user->hasPermission('manage_pending_bills'),
            'canViewLeads' => $user->hasPermission('viewAny_lead') || 
                              $user->hasPermission('create_lead') || 
                              $user->hasPermission('update_lead') || 
                              $user->hasPermission('delete_lead'),
            'canViewUsers' => $user->hasPermission('show_user') || 
                              $user->hasPermission('add_user') || 
                              $user->hasPermission('edit_user') || 
                              $user->hasPermission('delete_user'),
            'canViewCategories' => $user->hasPermission('viewAny_category') || 
                                   $user->hasPermission('create_category') || 
                                   $user->hasPermission('update_category') || 
                                   $user->hasPermission('delete_category'),

            'canManageSettings' => $user->hasPermission('manage_settings'),
            'canManageRoles' => $user->hasPermission('manage_roles'),
        ];
    }

    /**
     * Get default data with empty/zero values
     *
     * @return array
     */
    private function getDefaultData()
    {
        return [
            'userCount' => 0,
            'userGroupCount' => 0,
            'productCount' => 0,
            'categoryCount' => 0,
            'totalRevenue' => 0,
            'monthlyRevenue' => 0,
            'revenueGrowth' => 0,
            'totalOrders' => 0,
            'pendingOrders' => 0,
            'deliveredOrders' => 0,
            'todayOrders' => 0,
            'todayRevenue' => 0,
            'monthlyRevenueData' => [],
            'weeklyRevenueData' => [],
            'orderStatusData' => [],
            'recentOrders' => collect([]),
            'lowStockProducts' => collect([]),
            'outOfStockCount' => 0,
            'leadStats' => [
                'total' => 0,
                'new' => 0,
                'contacted' => 0,
                'qualified' => 0,
                'converted' => 0,
                'lost' => 0,
            ],
            'topProducts' => [],
            'pendingPayments' => 0,
        ];
    }

    /**
     * Get monthly revenue data for the last 12 months
     */
    private function getMonthlyRevenueData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_amount');
            
            $data[] = [
                'month' => $date->format('M Y'),
                'short_month' => $date->format('M'),
                'revenue' => round($revenue, 2)
            ];
        }
        return $data;
    }

    /**
     * Get weekly revenue data for the last 7 days
     */
    private function getWeeklyRevenueData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            
            $orders = ProformaInvoice::whereDate('created_at', $date)->count();
            
            $data[] = [
                'day' => $date->format('D'),
                'date' => $date->format('M d'),
                'revenue' => round($revenue, 2),
                'orders' => $orders
            ];
        }
        return $data;
    }

    /**
     * Get order status distribution
     */
    private function getOrderStatusDistribution()
    {
        return [
            ['status' => 'Draft', 'count' => ProformaInvoice::where('status', ProformaInvoice::STATUS_DRAFT)->count(), 'color' => '#6c757d'],
            ['status' => 'Approved', 'count' => ProformaInvoice::where('status', ProformaInvoice::STATUS_APPROVED)->count(), 'color' => '#0d6efd'],
            ['status' => 'Dispatch', 'count' => ProformaInvoice::where('status', ProformaInvoice::STATUS_DISPATCH)->count(), 'color' => '#0dcaf0'],
            ['status' => 'Out for Delivery', 'count' => ProformaInvoice::where('status', ProformaInvoice::STATUS_OUT_FOR_DELIVERY)->count(), 'color' => '#ffc107'],
            ['status' => 'Delivered', 'count' => ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)->count(), 'color' => '#198754'],
            ['status' => 'Return', 'count' => ProformaInvoice::where('status', ProformaInvoice::STATUS_RETURN)->count(), 'color' => '#dc3545'],
        ];
    }

    /**
     * Get top selling products from invoice data
     */
    private function getTopSellingProducts()
    {
        $invoices = ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)
            ->whereNotNull('invoice_data')
            ->get();
        
        $productSales = [];
        
        foreach ($invoices as $invoice) {
            $invoiceData = $invoice->invoice_data;
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    $productName = $item['name'] ?? $item['product_name'] ?? 'Unknown Product';
                    $quantity = $item['quantity'] ?? 1;
                    $total = $item['total'] ?? ($item['price'] ?? 0) * $quantity;
                    
                    if ($productId) {
                        if (!isset($productSales[$productId])) {
                            $productSales[$productId] = [
                                'id' => $productId,
                                'name' => $productName,
                                'quantity' => 0,
                                'revenue' => 0
                            ];
                        }
                        $productSales[$productId]['quantity'] += $quantity;
                        $productSales[$productId]['revenue'] += $total;
                    }
                }
            }
        }
        
        // Sort by quantity sold and take top 5
        usort($productSales, function($a, $b) {
            return $b['quantity'] - $a['quantity'];
        });
        
        return array_slice($productSales, 0, 5);
    }

    /**
     * Get dashboard data via AJAX for chart updates
     */
    public function getChartData(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to view orders (charts are order-related)
        if (!$user->hasPermission('manage_proforma_invoices')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $period = $request->get('period', 'weekly');
        
        if ($period === 'monthly') {
            $data = $this->getMonthlyRevenueData();
        } else {
            $data = $this->getWeeklyRevenueData();
        }
        
        return response()->json($data);
    }
}