<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Category;
use App\Models\ProformaInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductAnalyticsController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display the product analytics dashboard
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        // Date range filter
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Get vendor's product IDs
        $vendorProductIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        // Get overall statistics for vendor's products
        $totalViews = ProductView::whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->count();
            
        $uniqueVisitors = ProductView::whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->distinct('session_id')
            ->count('session_id');
            
        $productsViewed = ProductView::whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->distinct('product_id')
            ->count('product_id');

        // Most viewed products (vendor's only)
        $mostViewedProducts = ProductView::select('product_id', DB::raw('COUNT(*) as view_count'))
            ->whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('product_id')
            ->orderByDesc('view_count')
            ->limit(10)
            ->with('product:id,name,slug,selling_price,main_photo')
            ->get()
            ->filter(function ($item) {
                return $item->product !== null;
            });

        // Views by device type
        $viewsByDevice = ProductView::select('device_type', DB::raw('COUNT(*) as count'))
            ->whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        // Daily views trend
        $dailyViews = ProductView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
            )
            ->whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Hourly distribution
        $hourlyViews = ProductView::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as views')
            )
            ->whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[] = [
                'hour' => sprintf('%02d:00', $i),
                'views' => $hourlyViews->get($i)->views ?? 0
            ];
        }

        // Views by category (vendor's products only)
        $viewsByCategory = $this->getViewsByCategory($vendorProductIds, $startDateTime, $endDateTime);

        // User engagement stats
        $loggedInViews = ProductView::whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('user_id')
            ->count();
        $guestViews = $totalViews - $loggedInViews;

        // Recent views
        $recentViews = ProductView::with(['product:id,name,slug', 'user:id,name'])
            ->whereIn('product_id', $vendorProductIds)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Least viewed products
        $leastViewedProducts = ProductView::select('product_id', DB::raw('COUNT(*) as view_count'))
            ->whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('product_id')
            ->orderBy('view_count')
            ->limit(10)
            ->with('product:id,name,slug,selling_price,main_photo')
            ->get()
            ->filter(function ($item) {
                return $item->product !== null;
            });

        // Products with no views
        $viewedProductIds = ProductView::whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->distinct()
            ->pluck('product_id');
        
        $productsWithNoViews = Product::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->whereNotIn('id', $viewedProductIds)
            ->limit(10)
            ->get();

        // Conversion data
        $conversionData = $this->getConversionData($vendor->id, $vendorProductIds, $startDateTime, $endDateTime);

        return view('vendor.analytics.products', compact(
            'totalViews',
            'uniqueVisitors',
            'productsViewed',
            'mostViewedProducts',
            'viewsByDevice',
            'dailyViews',
            'hourlyData',
            'viewsByCategory',
            'loggedInViews',
            'guestViews',
            'recentViews',
            'leastViewedProducts',
            'productsWithNoViews',
            'conversionData',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get detailed analytics for a specific product
     */
    public function show(Request $request, Product $product)
    {
        $vendor = $this->getVendor();
        
        // Ensure product belongs to vendor
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        // Date range filter
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Total views for this product
        $totalViews = ProductView::forProduct($product->id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->count();

        // Unique visitors
        $uniqueVisitors = ProductView::forProduct($product->id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->distinct('session_id')
            ->count('session_id');

        // Daily views trend
        $dailyViews = ProductView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
            )
            ->forProduct($product->id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Views by device
        $viewsByDevice = ProductView::select('device_type', DB::raw('COUNT(*) as count'))
            ->forProduct($product->id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        // Views by browser
        $viewsByBrowser = ProductView::select('browser', DB::raw('COUNT(*) as count'))
            ->forProduct($product->id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->get();

        // Recent views with user info
        $recentViews = ProductView::with('user:id,name,email')
            ->forProduct($product->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Hourly distribution
        $hourlyViews = ProductView::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as views')
            )
            ->forProduct($product->id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[] = [
                'hour' => sprintf('%02d:00', $i),
                'views' => $hourlyViews->get($i)->views ?? 0
            ];
        }

        // Compare with previous period
        $previousStartDate = Carbon::parse($startDate)->subDays(Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);
        $previousEndDate = Carbon::parse($startDate)->subDay();
        
        $previousViews = ProductView::forProduct($product->id)
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->count();

        $viewsChange = $previousViews > 0 
            ? round((($totalViews - $previousViews) / $previousViews) * 100, 1)
            : ($totalViews > 0 ? 100 : 0);

        return view('vendor.analytics.product-detail', compact(
            'product',
            'totalViews',
            'uniqueVisitors',
            'dailyViews',
            'viewsByDevice',
            'viewsByBrowser',
            'recentViews',
            'hourlyData',
            'previousViews',
            'viewsChange',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $vendor = $this->getVendor();
        
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $vendorProductIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        $data = ProductView::select('product_id', DB::raw('COUNT(*) as total_views'), DB::raw('COUNT(DISTINCT session_id) as unique_views'))
            ->whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('product_id')
            ->with('product:id,name,slug,selling_price')
            ->orderByDesc('total_views')
            ->get()
            ->filter(function ($item) {
                return $item->product !== null;
            })
            ->map(function ($item) {
                return [
                    'Product ID' => $item->product_id,
                    'Product Name' => $item->product->name,
                    'Slug' => $item->product->slug,
                    'Price' => $item->product->selling_price,
                    'Total Views' => $item->total_views,
                    'Unique Views' => $item->unique_views,
                ];
            });

        $filename = 'product_analytics_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get views by category
     */
    private function getViewsByCategory($vendorProductIds, $startDateTime, $endDateTime)
    {
        $productViews = ProductView::select('product_id', DB::raw('COUNT(*) as views'))
            ->whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('product_id')
            ->get();

        $categoryViews = [];
        
        foreach ($productViews as $view) {
            $product = Product::find($view->product_id);
            if ($product && $product->product_categories) {
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id'])) {
                        $categoryId = $catData['category_id'];
                        if (!isset($categoryViews[$categoryId])) {
                            $category = Category::find($categoryId);
                            $categoryViews[$categoryId] = [
                                'name' => $category ? $category->name : 'Unknown',
                                'views' => 0
                            ];
                        }
                        $categoryViews[$categoryId]['views'] += $view->views;
                    }
                }
            }
        }

        uasort($categoryViews, function($a, $b) {
            return $b['views'] - $a['views'];
        });

        return array_slice($categoryViews, 0, 10, true);
    }

    /**
     * Get conversion data (views to purchases)
     */
    private function getConversionData($vendorId, $vendorProductIds, $startDateTime, $endDateTime)
    {
        // Get products that were viewed
        $viewedProducts = ProductView::select('product_id', DB::raw('COUNT(*) as views'))
            ->whereIn('product_id', $vendorProductIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Get products that were purchased (from delivered invoices)
        $purchasedProducts = [];
        $invoices = ProformaInvoice::where('status', ProformaInvoice::STATUS_DELIVERED)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('invoice_data')
            ->get();

        foreach ($invoices as $invoice) {
            $invoiceData = $invoice->invoice_data;
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId && $vendorProductIds->contains($productId)) {
                        if (!isset($purchasedProducts[$productId])) {
                            $purchasedProducts[$productId] = 0;
                        }
                        $purchasedProducts[$productId] += $item['quantity'] ?? 1;
                    }
                }
            }
        }

        // Calculate conversion rates
        $conversionData = [];
        foreach ($viewedProducts as $productId => $viewData) {
            $product = Product::find($productId);
            if ($product) {
                $purchases = $purchasedProducts[$productId] ?? 0;
                $conversionRate = $viewData->views > 0 ? round(($purchases / $viewData->views) * 100, 2) : 0;
                
                $conversionData[] = [
                    'product' => $product,
                    'views' => $viewData->views,
                    'purchases' => $purchases,
                    'conversion_rate' => $conversionRate
                ];
            }
        }

        usort($conversionData, function($a, $b) {
            return $b['views'] - $a['views'];
        });

        return array_slice($conversionData, 0, 10);
    }
}