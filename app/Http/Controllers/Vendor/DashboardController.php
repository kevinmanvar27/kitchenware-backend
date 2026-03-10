<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProformaInvoice;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the vendor dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get vendor based on user type (vendor owner or vendor staff)
        $vendor = $user->getActiveVendor();

        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }

        // Get user permissions for dashboard sections
        $permissions = $this->getUserPermissions($user);
        
        // Initialize all variables with default values
        $data = $this->getDefaultData();
        $data['vendor'] = $vendor;
        
        // Only fetch data based on user permissions
        if ($permissions['canViewProducts']) {
            $data['productCount'] = Product::where('vendor_id', $vendor->id)->count();
            $data['categoryCount'] = Category::where('vendor_id', $vendor->id)->count();
            
            // Low stock products
            $data['lowStockProducts'] = Product::where('vendor_id', $vendor->id)
                ->where('in_stock', true)
                ->whereColumn('stock_quantity', '<=', 'low_quantity_threshold')
                ->where('stock_quantity', '>', 0)
                ->orderBy('stock_quantity', 'asc')
                ->take(5)
                ->get();
            
            // Out of stock products count
            $data['outOfStockCount'] = Product::where('vendor_id', $vendor->id)
                ->where(function($query) {
                    $query->where('in_stock', false)
                          ->orWhere('stock_quantity', '<=', 0);
                })->count();
        }
        
        if ($permissions['canViewCategories'] && !$permissions['canViewProducts']) {
            // Only fetch category count if products permission is not available
            $data['categoryCount'] = Category::where('vendor_id', $vendor->id)->count();
        }
        
        if ($permissions['canViewOrders']) {
            // Get orders that contain this vendor's products
            $vendorOrders = $this->getVendorOrders($vendor->id);
            
            // Revenue statistics
            $data['totalRevenue'] = $vendorOrders['delivered']->sum('vendor_total');
            $data['monthlyRevenue'] = $vendorOrders['delivered']
                ->filter(function($order) {
                    return Carbon::parse($order['created_at'])->isCurrentMonth();
                })
                ->sum('vendor_total');
            
            $lastMonthRevenue = $vendorOrders['delivered']
                ->filter(function($order) {
                    $date = Carbon::parse($order['created_at']);
                    return $date->month === Carbon::now()->subMonth()->month 
                        && $date->year === Carbon::now()->subMonth()->year;
                })
                ->sum('vendor_total');
            
            $data['revenueGrowth'] = $lastMonthRevenue > 0 
                ? round((($data['monthlyRevenue'] - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) 
                : ($data['monthlyRevenue'] > 0 ? 100 : 0);

            // Order statistics
            $data['totalOrders'] = $vendorOrders['all']->count();
            $data['pendingOrders'] = $vendorOrders['pending']->count();
            $data['deliveredOrders'] = $vendorOrders['delivered']->count();
            
            // Today's statistics
            $data['todayOrders'] = $vendorOrders['all']
                ->filter(function($order) {
                    return Carbon::parse($order['created_at'])->isToday();
                })
                ->count();
            $data['todayRevenue'] = $vendorOrders['delivered']
                ->filter(function($order) {
                    return Carbon::parse($order['created_at'])->isToday();
                })
                ->sum('vendor_total');

            // Chart data
            $data['monthlyRevenueData'] = $this->getMonthlyRevenueData($vendorOrders['delivered']);
            $data['weeklyRevenueData'] = $this->getWeeklyRevenueData($vendorOrders['delivered']);
            $data['orderStatusData'] = $this->getOrderStatusDistribution($vendorOrders);

            // Recent orders
            $data['recentOrders'] = $vendorOrders['all']->take(5);
            
            // Top selling products (only if can view products too)
            if ($permissions['canViewProducts']) {
                $data['topProducts'] = $this->getTopSellingProducts($vendorOrders['delivered']);
            }
        }
        
        if ($permissions['canViewLeads']) {
            // Lead statistics
            $data['leadStats'] = [
                'total' => Lead::where('vendor_id', $vendor->id)->count(),
                'new' => Lead::where('vendor_id', $vendor->id)->where('status', 'new')->count(),
                'contacted' => Lead::where('vendor_id', $vendor->id)->where('status', 'contacted')->count(),
                'followup' => Lead::where('vendor_id', $vendor->id)->where('status', 'followup')->count(),
                'qualified' => Lead::where('vendor_id', $vendor->id)->where('status', 'qualified')->count(),
                'converted' => Lead::where('vendor_id', $vendor->id)->where('status', 'converted')->count(),
                'lost' => Lead::where('vendor_id', $vendor->id)->where('status', 'lost')->count(),
            ];
        }
        
        if ($permissions['canViewPendingBills']) {
            // Pending payments for this vendor's orders
            $data['pendingPayments'] = $this->getVendorPendingPayments($vendor->id);
        }

        // Get vendor wallet and earnings data
        $vendorWallet = $vendor->getOrCreateWallet();
        $totalEarnings = $vendor->earnings()->sum('vendor_earning');
        $pendingEarnings = $vendor->earnings()->where('status', 'pending')->sum('vendor_earning');
        $confirmedEarnings = $vendor->earnings()->where('status', 'confirmed')->sum('vendor_earning');
        $paidEarnings = $vendor->earnings()->where('status', 'paid')->sum('vendor_earning');

        // Commission info (always show for vendor)
        $data['commissionRate'] = $vendor->commission_rate ?? 0;
        $data['totalCommission'] = $data['totalRevenue'] * ($data['commissionRate'] / 100);
        $data['netEarnings'] = $totalEarnings; // Use actual earnings from database
        $data['walletBalance'] = [
            'total_earned' => $vendorWallet->total_earned,
            'pending_amount' => $vendorWallet->pending_amount,
            'total_paid' => $vendorWallet->total_paid,
            'earnings_breakdown' => [
                'pending' => $pendingEarnings,
                'confirmed' => $confirmedEarnings,
                'paid' => $paidEarnings
            ]
        ];

        return view('vendor.dashboard.index', $data);
    }

    /**
     * Get user permissions for dashboard sections
     *
     * @param \App\Models\User $user
     * @return array
     */
    private function getUserPermissions($user)
    {
        return [
            'canViewProducts' => $user->hasVendorPermission('products'),
            'canViewCategories' => $user->hasVendorPermission('categories'),
            'canViewOrders' => $user->hasVendorPermission('invoices'),
            'canViewLeads' => $user->hasVendorPermission('leads'),
            'canViewPendingBills' => $user->hasVendorPermission('pending_bills'),
            'canViewReports' => $user->hasVendorPermission('reports'),
            'canViewAnalytics' => $user->hasVendorPermission('analytics'),
            'canViewCustomers' => $user->hasVendorPermission('customers'),
            'canViewStaff' => $user->hasVendorPermission('staff'),

            'canViewCoupons' => $user->hasVendorPermission('coupons'),
            'canViewSalary' => $user->hasVendorPermission('salary'),
            'canViewAttendance' => $user->hasVendorPermission('attendance'),
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
            'vendor' => null,
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
            'topProducts' => [],
            'leadStats' => [
                'total' => 0,
                'new' => 0,
                'contacted' => 0,
                'qualified' => 0,
                'converted' => 0,
                'lost' => 0,
            ],
            'pendingPayments' => 0,
            'commissionRate' => 0,
            'totalCommission' => 0,
            'netEarnings' => 0,
            'walletBalance' => [
                'total_earned' => 0,
                'pending_amount' => 0,
                'total_paid' => 0,
                'earnings_breakdown' => [
                    'pending' => 0,
                    'confirmed' => 0,
                    'paid' => 0
                ]
            ],
        ];
    }

    /**
     * Get pending payments for vendor's orders
     */
    private function getVendorPendingPayments($vendorId)
    {
        $allInvoices = ProformaInvoice::whereNotNull('invoice_data')
            ->where(function($query) {
                $query->where('payment_status', 'pending')
                      ->orWhere('payment_status', 'partial');
            })
            ->get();
        
        $pendingTotal = 0;
        
        foreach ($allInvoices as $invoice) {
            $invoiceData = $invoice->invoice_data;
            $vendorTotal = 0;
            $hasVendorProducts = false;
            
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendorId) {
                            $hasVendorProducts = true;
                            $vendorTotal += $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
                        }
                    }
                }
            }
            
            if ($hasVendorProducts) {
                // Calculate pending amount proportionally
                $invoiceTotal = $invoice->total_amount ?? 0;
                $paidAmount = $invoice->paid_amount ?? 0;
                $pendingRatio = $invoiceTotal > 0 ? (($invoiceTotal - $paidAmount) / $invoiceTotal) : 1;
                $pendingTotal += $vendorTotal * $pendingRatio;
            }
        }
        
        return $pendingTotal;
    }

    /**
     * Get orders containing vendor's products
     */
    private function getVendorOrders($vendorId)
    {
        $allInvoices = ProformaInvoice::whereNotNull('invoice_data')->get();
        
        $vendorOrders = collect();
        
        foreach ($allInvoices as $invoice) {
            $invoiceData = $invoice->invoice_data;
            $vendorTotal = 0;
            $hasVendorProducts = false;
            
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendorId) {
                            $hasVendorProducts = true;
                            $vendorTotal += $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
                        }
                    }
                }
            }
            
            if ($hasVendorProducts) {
                $vendorOrders->push([
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? 'INV-' . $invoice->id,
                    'user' => $invoice->user,
                    'status' => $invoice->status,
                    'vendor_total' => $vendorTotal,
                    'created_at' => $invoice->created_at,
                ]);
            }
        }
        
        return [
            'all' => $vendorOrders->sortByDesc('created_at'),
            'delivered' => $vendorOrders->where('status', ProformaInvoice::STATUS_DELIVERED),
            'pending' => $vendorOrders->whereIn('status', [
                ProformaInvoice::STATUS_DRAFT,
                ProformaInvoice::STATUS_APPROVED,
                ProformaInvoice::STATUS_DISPATCH,
                ProformaInvoice::STATUS_OUT_FOR_DELIVERY
            ]),
        ];
    }

    /**
     * Get monthly revenue data for the last 12 months
     */
    private function getMonthlyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = $deliveredOrders
                ->filter(function($order) use ($date) {
                    $orderDate = Carbon::parse($order['created_at']);
                    return $orderDate->month === $date->month && $orderDate->year === $date->year;
                })
                ->sum('vendor_total');
            
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
    private function getWeeklyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = $deliveredOrders
                ->filter(function($order) use ($date) {
                    return Carbon::parse($order['created_at'])->isSameDay($date);
                })
                ->sum('vendor_total');
            
            $orders = $deliveredOrders
                ->filter(function($order) use ($date) {
                    return Carbon::parse($order['created_at'])->isSameDay($date);
                })
                ->count();
            
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
    private function getOrderStatusDistribution($vendorOrders)
    {
        return [
            ['status' => 'Draft', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DRAFT)->count(), 'color' => '#6c757d'],
            ['status' => 'Approved', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_APPROVED)->count(), 'color' => '#0d6efd'],
            ['status' => 'Dispatch', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DISPATCH)->count(), 'color' => '#0dcaf0'],
            ['status' => 'Out for Delivery', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_OUT_FOR_DELIVERY)->count(), 'color' => '#ffc107'],
            ['status' => 'Delivered', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DELIVERED)->count(), 'color' => '#198754'],
            ['status' => 'Return', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_RETURN)->count(), 'color' => '#dc3545'],
        ];
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts($deliveredOrders)
    {
        $productSales = [];
        $vendor = Auth::user()->getActiveVendor();
        
        foreach ($deliveredOrders as $order) {
            $invoice = ProformaInvoice::find($order['id']);
            if (!$invoice) continue;
            
            $invoiceData = $invoice->invoice_data;
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendor->id) {
                            $productName = $item['name'] ?? $item['product_name'] ?? 'Unknown Product';
                            $quantity = $item['quantity'] ?? 1;
                            $total = $item['total'] ?? ($item['price'] ?? 0) * $quantity;
                            
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
        }
        
        usort($productSales, function($a, $b) {
            return $b['quantity'] - $a['quantity'];
        });
        
        return array_slice($productSales, 0, 5);
    }

    /**
     * Show pending approval page
     */
    public function pending()
    {
        $user = Auth::user();
        $vendor = $user->getActiveVendor();
        
        if ($vendor && $vendor->isApproved()) {
            return redirect()->route('vendor.dashboard');
        }
        
        return view('vendor.auth.pending', compact('vendor'));
    }

    /**
     * Show rejected page
     */
    public function rejected()
    {
        $user = Auth::user();
        $vendor = $user->getActiveVendor();
        
        if ($vendor && $vendor->isApproved()) {
            return redirect()->route('vendor.dashboard');
        }
        
        return view('vendor.auth.rejected', compact('vendor'));
    }

    /**
     * Show suspended page
     */
    public function suspended()
    {
        $user = Auth::user();
        $vendor = $user->getActiveVendor();
        
        if ($vendor && $vendor->isApproved()) {
            return redirect()->route('vendor.dashboard');
        }
        
        return view('vendor.auth.suspended', compact('vendor'));
    }

    /**
     * Get dashboard data via AJAX for chart updates
     */
    public function getChartData(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to view orders (charts are order-related)
        if (!$user->hasVendorPermission('invoices')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $period = $request->get('period', 'weekly');
        $vendor = $user->getActiveVendor();
        $vendorOrders = $this->getVendorOrders($vendor->id);
        
        if ($period === 'monthly') {
            $data = $this->getMonthlyRevenueData($vendorOrders['delivered']);
        } else {
            $data = $this->getWeeklyRevenueData($vendorOrders['delivered']);
        }
        
        return response()->json($data);
    }
}
