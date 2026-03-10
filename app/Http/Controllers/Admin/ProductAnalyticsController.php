<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Category;
use App\Models\ProformaInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductAnalyticsController extends Controller
{
    /**
     * Display the product analytics dashboard
     */
    public function index(Request $request)
    {
        // Date range filter
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Get overall statistics
        $totalViews = ProductView::whereBetween('created_at', [$startDateTime, $endDateTime])->count();
        $uniqueVisitors = ProductView::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->distinct('session_id')
            ->count('session_id');
        $productsViewed = ProductView::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->distinct('product_id')
            ->count('product_id');

        // Most viewed products
        $mostViewedProducts = ProductView::select('product_id', DB::raw('COUNT(*) as view_count'))
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
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        // Views by browser
        $viewsByBrowser = ProductView::select('browser', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Daily views trend
        $dailyViews = ProductView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
            )
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Hourly distribution (for today or selected period)
        $hourlyViews = ProductView::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as views')
            )
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        // Fill in missing hours
        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[] = [
                'hour' => sprintf('%02d:00', $i),
                'views' => $hourlyViews->get($i)->views ?? 0
            ];
        }

        // Views by category
        $viewsByCategory = $this->getViewsByCategory($startDateTime, $endDateTime);

        // Views by country
        $viewsByCountry = ProductView::select('country', 'country_code', DB::raw('COUNT(*) as view_count'))
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country', 'country_code')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        // Views by city
        $viewsByCity = ProductView::select('city', 'country', 'country_code', DB::raw('COUNT(*) as view_count'))
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city', 'country', 'country_code')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        // User engagement stats
        $loggedInViews = ProductView::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('user_id')
            ->count();
        $guestViews = $totalViews - $loggedInViews;

        // Recent views
        $recentViews = ProductView::with(['product:id,name,slug', 'user:id,name'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Least viewed products (products with views but low count)
        $leastViewedProducts = ProductView::select('product_id', DB::raw('COUNT(*) as view_count'))
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
        $viewedProductIds = ProductView::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->distinct()
            ->pluck('product_id');
        
        $productsWithNoViews = Product::where('status', 'published')
            ->whereNotIn('id', $viewedProductIds)
            ->limit(10)
            ->get();

        // Conversion rate (views to cart/purchase)
        $conversionData = $this->getConversionData($startDateTime, $endDateTime);

        return view('admin.analytics.products', compact(
            'totalViews',
            'uniqueVisitors',
            'productsViewed',
            'mostViewedProducts',
            'viewsByDevice',
            'viewsByBrowser',
            'dailyViews',
            'hourlyData',
            'viewsByCategory',
            'viewsByCountry',
            'viewsByCity',
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

        // Referrer sources
        $referrerSources = ProductView::select('referrer', DB::raw('COUNT(*) as count'))
            ->forProduct($product->id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Views by country for this product
        $productCountryViews = ProductView::select('country', 'country_code', DB::raw('COUNT(*) as view_count'))
            ->forProduct($product->id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country', 'country_code')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        // Views by city for this product
        $productCityViews = ProductView::select('city', 'country', 'country_code', DB::raw('COUNT(*) as view_count'))
            ->forProduct($product->id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city', 'country', 'country_code')
            ->orderByDesc('view_count')
            ->limit(10)
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

        return view('admin.analytics.product-detail', compact(
            'product',
            'totalViews',
            'uniqueVisitors',
            'dailyViews',
            'viewsByDevice',
            'viewsByBrowser',
            'referrerSources',
            'productCountryViews',
            'productCityViews',
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
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $data = ProductView::select('product_id', DB::raw('COUNT(*) as total_views'), DB::raw('COUNT(DISTINCT session_id) as unique_views'))
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
            
            // Header row
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            
            // Data rows
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
    private function getViewsByCategory($startDateTime, $endDateTime)
    {
        $productViews = ProductView::select('product_id', DB::raw('COUNT(*) as views'))
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

        // Sort by views and return top 10
        uasort($categoryViews, function($a, $b) {
            return $b['views'] - $a['views'];
        });

        return array_slice($categoryViews, 0, 10, true);
    }

    /**
     * Get conversion data (views to purchases)
     */
    private function getConversionData($startDateTime, $endDateTime)
    {
        // Get products that were viewed
        $viewedProducts = ProductView::select('product_id', DB::raw('COUNT(*) as views'))
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
                    if ($productId) {
                        if (!isset($purchasedProducts[$productId])) {
                            $purchasedProducts[$productId] = 0;
                        }
                        $purchasedProducts[$productId] += $item['quantity'] ?? 1;
                    }
                }
            }
        }

        // Calculate conversion rates for top viewed products
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

        // Sort by views and take top 10
        usort($conversionData, function($a, $b) {
            return $b['views'] - $a['views'];
        });

        return array_slice($conversionData, 0, 10);
    }

    /**
     * Get chart data via AJAX
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'daily');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $productId = $request->get('product_id');
        
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $query = ProductView::query();
        
        if ($productId) {
            $query->where('product_id', $productId);
        }

        $query->whereBetween('created_at', [$startDateTime, $endDateTime]);

        switch ($type) {
            case 'hourly':
                $data = $query->select(
                        DB::raw('HOUR(created_at) as label'),
                        DB::raw('COUNT(*) as views')
                    )
                    ->groupBy(DB::raw('HOUR(created_at)'))
                    ->orderBy('label')
                    ->get();
                break;

            case 'weekly':
                $data = $query->select(
                        DB::raw('YEARWEEK(created_at) as label'),
                        DB::raw('COUNT(*) as views')
                    )
                    ->groupBy(DB::raw('YEARWEEK(created_at)'))
                    ->orderBy('label')
                    ->get();
                break;

            default: // daily
                $data = $query->select(
                        DB::raw('DATE(created_at) as label'),
                        DB::raw('COUNT(*) as views')
                    )
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('label')
                    ->get();
                break;
        }

        return response()->json($data);
    }
}
