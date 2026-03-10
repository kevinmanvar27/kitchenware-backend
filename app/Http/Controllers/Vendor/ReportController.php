<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\ProformaInvoice;
use App\Models\Vendor;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        $user = Auth::user();
        return $user->vendor ?? $user->vendorStaff?->vendor;
    }

    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }

        // Date range filter
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Get vendor orders
        $vendorOrders = $this->getVendorOrders($vendor->id, $dateFrom, $dateTo);
        
        // Calculate statistics
        $stats = $this->calculateStats($vendorOrders, $vendor);
        
        // Get chart data
        $revenueChartData = $this->getRevenueChartData($vendorOrders['delivered'], $dateFrom, $dateTo);
        $ordersChartData = $this->getOrdersChartData($vendorOrders['all'], $dateFrom, $dateTo);
        $topProducts = $this->getTopSellingProducts($vendorOrders['delivered'], $vendor->id);
        $categoryBreakdown = $this->getCategoryBreakdown($vendorOrders['delivered'], $vendor->id);

        return view('vendor.reports.index', compact(
            'vendor',
            'stats',
            'revenueChartData',
            'ordersChartData',
            'topProducts',
            'categoryBreakdown',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Get orders containing vendor's products
     */
    private function getVendorOrders($vendorId, $dateFrom = null, $dateTo = null)
    {
        $query = ProformaInvoice::with('user')->whereNotNull('invoice_data');
        
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        $allInvoices = $query->orderBy('created_at', 'desc')->get();
        
        $vendorOrders = collect();
        
        foreach ($allInvoices as $invoice) {
            $invoiceData = $invoice->invoice_data;
            $vendorTotal = 0;
            $hasVendorProducts = false;
            $vendorProductCount = 0;
            $vendorItems = [];
            
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendorId) {
                            $hasVendorProducts = true;
                            $vendorProductCount++;
                            $itemTotal = $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
                            $vendorTotal += $itemTotal;
                            $vendorItems[] = [
                                'product_id' => $productId,
                                'category_id' => $product->category_id,
                                'quantity' => $item['quantity'] ?? 1,
                                'total' => $itemTotal,
                            ];
                        }
                    }
                }
            }
            
            if ($hasVendorProducts) {
                $vendorOrders->push([
                    'id' => $invoice->id,
                    'status' => $invoice->status,
                    'vendor_total' => $vendorTotal,
                    'vendor_product_count' => $vendorProductCount,
                    'vendor_items' => $vendorItems,
                    'created_at' => $invoice->created_at,
                ]);
            }
        }
        
        return [
            'all' => $vendorOrders,
            'delivered' => $vendorOrders->where('status', ProformaInvoice::STATUS_DELIVERED),
            'pending' => $vendorOrders->whereIn('status', [
                ProformaInvoice::STATUS_DRAFT,
                ProformaInvoice::STATUS_APPROVED,
                ProformaInvoice::STATUS_DISPATCH,
                ProformaInvoice::STATUS_OUT_FOR_DELIVERY
            ]),
            'returned' => $vendorOrders->where('status', ProformaInvoice::STATUS_RETURN),
        ];
    }

    /**
     * Calculate statistics
     */
    private function calculateStats($vendorOrders, $vendor)
    {
        $totalRevenue = $vendorOrders['delivered']->sum('vendor_total');
        $commissionRate = $vendor->commission_rate ?? 0;
        $totalCommission = $totalRevenue * ($commissionRate / 100);
        $netEarnings = $totalRevenue - $totalCommission;
        
        $totalOrders = $vendorOrders['all']->count();
        $deliveredOrders = $vendorOrders['delivered']->count();
        $pendingOrders = $vendorOrders['pending']->count();
        $returnedOrders = $vendorOrders['returned']->count();
        
        $avgOrderValue = $deliveredOrders > 0 ? $totalRevenue / $deliveredOrders : 0;
        $totalProductsSold = $vendorOrders['delivered']->sum('vendor_product_count');
        
        return [
            'total_revenue' => $totalRevenue,
            'total_commission' => $totalCommission,
            'net_earnings' => $netEarnings,
            'commission_rate' => $commissionRate,
            'total_orders' => $totalOrders,
            'delivered_orders' => $deliveredOrders,
            'pending_orders' => $pendingOrders,
            'returned_orders' => $returnedOrders,
            'avg_order_value' => $avgOrderValue,
            'total_products_sold' => $totalProductsSold,
        ];
    }

    /**
     * Get revenue chart data
     */
    private function getRevenueChartData($deliveredOrders, $dateFrom, $dateTo)
    {
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $diffInDays = $start->diffInDays($end);
        
        $data = [];
        
        if ($diffInDays <= 31) {
            // Daily data
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $revenue = $deliveredOrders
                    ->filter(function($order) use ($date) {
                        return Carbon::parse($order['created_at'])->isSameDay($date);
                    })
                    ->sum('vendor_total');
                
                $data[] = [
                    'label' => $date->format('M d'),
                    'value' => round($revenue, 2)
                ];
            }
        } else {
            // Weekly data
            for ($date = $start->copy()->startOfWeek(); $date->lte($end); $date->addWeek()) {
                $weekEnd = $date->copy()->endOfWeek();
                $revenue = $deliveredOrders
                    ->filter(function($order) use ($date, $weekEnd) {
                        $orderDate = Carbon::parse($order['created_at']);
                        return $orderDate->gte($date) && $orderDate->lte($weekEnd);
                    })
                    ->sum('vendor_total');
                
                $data[] = [
                    'label' => $date->format('M d'),
                    'value' => round($revenue, 2)
                ];
            }
        }
        
        return $data;
    }

    /**
     * Get orders chart data
     */
    private function getOrdersChartData($allOrders, $dateFrom, $dateTo)
    {
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $diffInDays = $start->diffInDays($end);
        
        $data = [];
        
        if ($diffInDays <= 31) {
            // Daily data
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $orders = $allOrders
                    ->filter(function($order) use ($date) {
                        return Carbon::parse($order['created_at'])->isSameDay($date);
                    })
                    ->count();
                
                $data[] = [
                    'label' => $date->format('M d'),
                    'value' => $orders
                ];
            }
        } else {
            // Weekly data
            for ($date = $start->copy()->startOfWeek(); $date->lte($end); $date->addWeek()) {
                $weekEnd = $date->copy()->endOfWeek();
                $orders = $allOrders
                    ->filter(function($order) use ($date, $weekEnd) {
                        $orderDate = Carbon::parse($order['created_at']);
                        return $orderDate->gte($date) && $orderDate->lte($weekEnd);
                    })
                    ->count();
                
                $data[] = [
                    'label' => $date->format('M d'),
                    'value' => $orders
                ];
            }
        }
        
        return $data;
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts($deliveredOrders, $vendorId)
    {
        $productSales = [];
        
        foreach ($deliveredOrders as $order) {
            foreach ($order['vendor_items'] as $item) {
                $productId = $item['product_id'];
                if (!isset($productSales[$productId])) {
                    $product = Product::with('mainPhoto')->find($productId);
                    $productSales[$productId] = [
                        'id' => $productId,
                        'name' => $product->name ?? 'Unknown Product',
                        'image' => $product && $product->mainPhoto ? $product->mainPhoto->path : null,
                        'quantity' => 0,
                        'revenue' => 0
                    ];
                }
                $productSales[$productId]['quantity'] += $item['quantity'];
                $productSales[$productId]['revenue'] += $item['total'];
            }
        }
        
        usort($productSales, function($a, $b) {
            return $b['revenue'] - $a['revenue'];
        });
        
        return array_slice($productSales, 0, 10);
    }

    /**
     * Get category breakdown
     */
    private function getCategoryBreakdown($deliveredOrders, $vendorId)
    {
        $categorySales = [];
        
        foreach ($deliveredOrders as $order) {
            foreach ($order['vendor_items'] as $item) {
                $categoryId = $item['category_id'];
                if (!isset($categorySales[$categoryId])) {
                    $category = \App\Models\Category::find($categoryId);
                    $categorySales[$categoryId] = [
                        'id' => $categoryId,
                        'name' => $category->name ?? 'Uncategorized',
                        'quantity' => 0,
                        'revenue' => 0
                    ];
                }
                $categorySales[$categoryId]['quantity'] += $item['quantity'];
                $categorySales[$categoryId]['revenue'] += $item['total'];
            }
        }
        
        usort($categorySales, function($a, $b) {
            return $b['revenue'] - $a['revenue'];
        });
        
        return array_slice($categorySales, 0, 10);
    }

    /**
     * Export report to CSV
     */
    public function export(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }

        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        
        $vendorOrders = $this->getVendorOrders($vendor->id, $dateFrom, $dateTo);
        $stats = $this->calculateStats($vendorOrders, $vendor);
        $topProducts = $this->getTopSellingProducts($vendorOrders['delivered'], $vendor->id);
        
        $filename = 'vendor_report_' . $dateFrom . '_to_' . $dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($stats, $topProducts, $dateFrom, $dateTo, $vendor) {
            $file = fopen('php://output', 'w');
            
            // Report header
            fputcsv($file, ['Vendor Report']);
            fputcsv($file, ['Store:', $vendor->store_name]);
            fputcsv($file, ['Period:', $dateFrom . ' to ' . $dateTo]);
            fputcsv($file, []);
            
            // Summary
            fputcsv($file, ['Summary']);
            fputcsv($file, ['Total Revenue', '₹' . number_format($stats['total_revenue'], 2)]);
            fputcsv($file, ['Commission (' . $stats['commission_rate'] . '%)', '₹' . number_format($stats['total_commission'], 2)]);
            fputcsv($file, ['Net Earnings', '₹' . number_format($stats['net_earnings'], 2)]);
            fputcsv($file, ['Total Orders', $stats['total_orders']]);
            fputcsv($file, ['Delivered Orders', $stats['delivered_orders']]);
            fputcsv($file, ['Pending Orders', $stats['pending_orders']]);
            fputcsv($file, ['Returned Orders', $stats['returned_orders']]);
            fputcsv($file, ['Average Order Value', '₹' . number_format($stats['avg_order_value'], 2)]);
            fputcsv($file, ['Total Products Sold', $stats['total_products_sold']]);
            fputcsv($file, []);
            
            // Top Products
            fputcsv($file, ['Top Selling Products']);
            fputcsv($file, ['Product Name', 'Quantity Sold', 'Revenue']);
            foreach ($topProducts as $product) {
                fputcsv($file, [
                    $product['name'],
                    $product['quantity'],
                    '₹' . number_format($product['revenue'], 2)
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
