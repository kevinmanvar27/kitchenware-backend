<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Exception;

class DatabaseExportImportController extends Controller
{
    /**
     * Display export page with table selection
     */
    public function exportIndex()
    {
        $user = auth()->user();
        
        // Get all tables from database
        $tables = $this->getAllTables();
        
        // Group tables by category for better organization
        $groupedTables = $this->groupTables($tables);
        
        // Get vendors list for super admin
        $vendors = [];
        if ($user->isSuperAdmin()) {
            $vendors = \App\Models\Vendor::with('user')
                ->where('status', 'approved')
                ->orderBy('store_name')
                ->get();
        } elseif ($user->isVendor() || $user->isVendorStaff()) {
            // For vendors, we'll auto-select their vendor in the export method
            // No need to show vendor selection dropdown
        } else {
            // Other users don't have access
            abort(403, 'You do not have permission to export database.');
        }
        
        return view('admin.database.export', compact('groupedTables', 'vendors'));
    }

    /**
     * Display import page
     */
    public function importIndex()
    {
        // Get list of previous exports
        $exports = $this->getPreviousExports();
        
        return view('admin.database.import', compact('exports'));
    }

    /**
     * Export selected tables to SQL file
     */
    public function export(Request $request)
    {
        $request->validate([
            'tables' => 'required|array|min:1',
            'tables.*' => 'required|string',
            'include_data' => 'boolean',
            'include_structure' => 'boolean',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);

        try {
            $user = auth()->user();
            $vendorId = $request->input('vendor_id');
            
            // Check vendor access permissions
            if (!$user->isSuperAdmin()) {
                // If not super admin, check if user is vendor or vendor staff
                if ($user->isVendor()) {
                    $vendor = $user->vendor;
                    if ($vendor) {
                        $vendorId = $vendor->id;
                    } else {
                        return redirect()->back()->with('error', 'Vendor profile not found.');
                    }
                } elseif ($user->isVendorStaff()) {
                    $staffRecord = $user->vendorStaff;
                    if ($staffRecord && $staffRecord->vendor) {
                        $vendorId = $staffRecord->vendor->id;
                    } else {
                        return redirect()->back()->with('error', 'Vendor staff profile not found.');
                    }
                } else {
                    return redirect()->back()->with('error', 'You do not have permission to export database.');
                }
            }
            
            $tables = $request->input('tables');
            $includeData = $request->input('include_data', true);
            $includeStructure = $request->input('include_structure', true);

            // Generate SQL dump with vendor filtering
            $sqlContent = $this->generateSqlDump($tables, $includeData, $includeStructure, $vendorId);

            // Create filename with timestamp and vendor info
            $filenameParts = ['database_export'];
            if ($vendorId) {
                $vendor = \App\Models\Vendor::find($vendorId);
                if ($vendor) {
                    $filenameParts[] = 'vendor_' . $vendor->id . '_' . \Illuminate\Support\Str::slug($vendor->store_name);
                }
            } else {
                $filenameParts[] = 'full';
            }
            $filenameParts[] = date('Y-m-d_H-i-s');
            $filename = implode('_', $filenameParts) . '.sql';
            
            // Ensure exports directory exists
            $exportsDir = storage_path('app/exports');
            if (!file_exists($exportsDir)) {
                if (!mkdir($exportsDir, 0755, true)) {
                    throw new Exception('Failed to create exports directory. Please check storage permissions.');
                }
            }
            
            // Full file path
            $fullPath = $exportsDir . '/' . $filename;
            
            // Save to storage
            $saved = file_put_contents($fullPath, $sqlContent);
            
            if ($saved === false) {
                throw new Exception('Failed to save export file. Please check storage permissions.');
            }
            
            // Verify file exists
            if (!file_exists($fullPath)) {
                throw new Exception('Export file was not created. Please check storage permissions.');
            }

            // Download file and delete after send
            return response()->download($fullPath, $filename)->deleteFileAfterSend(true);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation failed. Please check your input.');
        } catch (Exception $e) {
            \Log::error('Database Export Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'vendor_id' => $vendorId ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Import SQL file
     */
    public function import(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt|max:51200', // Max 50MB
        ]);

        try {
            $file = $request->file('sql_file');
            
            // Read SQL file content
            $sqlContent = file_get_contents($file->getRealPath());

            // Split SQL statements
            $statements = $this->splitSqlStatements($sqlContent);

            // Execute statements
            DB::beginTransaction();
            
            $executed = 0;
            $failed = 0;
            $errors = [];

            foreach ($statements as $statement) {
                if (trim($statement) !== '') {
                    try {
                        DB::statement($statement);
                        $executed++;
                    } catch (Exception $e) {
                        $failed++;
                        $errors[] = substr($statement, 0, 100) . '... - Error: ' . $e->getMessage();
                    }
                }
            }

            if ($failed > 0) {
                DB::rollBack();
                return redirect()->back()->with('error', "Import failed. $failed statements failed. First error: " . ($errors[0] ?? 'Unknown error'));
            }

            DB::commit();

            return redirect()->back()->with('success', "Import successful! $executed statements executed.");

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Get all tables from database
     */
    private function getAllTables()
    {
        $database = env('DB_DATABASE');
        $tables = DB::select('SHOW TABLES');
        
        return array_map(function($table) use ($database) {
            // Convert object to array to get the first value
            $tableArray = (array) $table;
            return reset($tableArray); // Get the first (and only) value
        }, $tables);
    }

    /**
     * Group tables by category
     */
    private function groupTables($tables)
    {
        $groups = [
            'Products' => [],
            'Users & Authentication' => [],
            'Orders & Invoices' => [],
            'Settings & Configuration' => [],
            'Categories & Attributes' => [],
            'Media & Files' => [],
            'Payments & Transactions' => [],
            'Tasks & Activities' => [],
            'Other' => [],
        ];

        foreach ($tables as $table) {
            $added = false;

            // Products related
            if (preg_match('/(product|inventory|stock)/i', $table)) {
                $groups['Products'][] = $table;
                $added = true;
            }
            
            // Users related
            if (!$added && preg_match('/(user|role|permission|auth|password|session)/i', $table)) {
                $groups['Users & Authentication'][] = $table;
                $added = true;
            }
            
            // Orders related
            if (!$added && preg_match('/(order|invoice|proforma|cart|bill)/i', $table)) {
                $groups['Orders & Invoices'][] = $table;
                $added = true;
            }
            
            // Settings
            if (!$added && preg_match('/(setting|config|option|page)/i', $table)) {
                $groups['Settings & Configuration'][] = $table;
                $added = true;
            }
            
            // Categories
            if (!$added && preg_match('/(categor|attribute|tag)/i', $table)) {
                $groups['Categories & Attributes'][] = $table;
                $added = true;
            }
            
            // Media
            if (!$added && preg_match('/(media|image|file|upload)/i', $table)) {
                $groups['Media & Files'][] = $table;
                $added = true;
            }
            
            // Payments
            if (!$added && preg_match('/(payment|transaction|payout|wallet|coupon|referral|subscription)/i', $table)) {
                $groups['Payments & Transactions'][] = $table;
                $added = true;
            }
            
            // Tasks & Activities
            if (!$added && preg_match('/(task|activity|log|notification|attendance|salary|lead)/i', $table)) {
                $groups['Tasks & Activities'][] = $table;
                $added = true;
            }
            
            // Other
            if (!$added) {
                $groups['Other'][] = $table;
            }
        }

        // Remove empty groups
        return array_filter($groups, function($group) {
            return !empty($group);
        });
    }

    /**
     * Generate SQL dump for selected tables
     */
    private function generateSqlDump($tables, $includeData = true, $includeStructure = true, $vendorId = null)
    {
        $sql = "-- Database Export\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        
        if ($vendorId) {
            $vendor = \App\Models\Vendor::find($vendorId);
            if ($vendor) {
                $sql .= "-- Vendor: {$vendor->store_name} (ID: {$vendorId})\n";
            }
        } else {
            $sql .= "-- Full Database Export\n";
        }
        
        $sql .= "-- Tables: " . implode(', ', $tables) . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table: $table\n";
            $sql .= "-- --------------------------------------------------------\n\n";

            // Export structure
            if ($includeStructure) {
                $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                
                $createTable = DB::select("SHOW CREATE TABLE `$table`");
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
            }

            // Export data
            if ($includeData) {
                $query = DB::table($table);
                
                // Apply vendor filtering for specific tables
                if ($vendorId) {
                    $query = $this->applyVendorFilter($query, $table, $vendorId);
                }
                
                $rows = $query->get();
                
                if ($rows->count() > 0) {
                    foreach ($rows as $row) {
                        $row = (array) $row;
                        $columns = array_keys($row);
                        $values = array_values($row);
                        
                        // Escape values
                        $values = array_map(function($value) {
                            if (is_null($value)) {
                                return 'NULL';
                            }
                            return "'" . addslashes($value) . "'";
                        }, $values);
                        
                        $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }
    
    /**
     * Apply vendor filter to query based on table
     */
    private function applyVendorFilter($query, $table, $vendorId)
    {
        // Tables with direct vendor_id column
        $directVendorTables = [
            'products',
            'proforma_invoices',
            'without_gst_invoices',
            'vendor_earnings',
            'vendor_wallets',
            'vendor_payouts',
            'vendor_bank_accounts',
            'vendor_customers',
            'vendor_feature_settings',
            'vendor_reviews',
            'vendor_followers',
            'coupons',
            'push_notifications',
            'scheduled_notifications',
            'vendor_staff',
        ];
        
        // Check if table has vendor_id column
        if (in_array($table, $directVendorTables)) {
            $query->where('vendor_id', $vendorId);
        }
        
        // Special cases
        switch ($table) {
            case 'vendors':
                // Only export the specific vendor
                $query->where('id', $vendorId);
                break;
                
            case 'users':
                // Export vendor owner and their staff
                $vendor = \App\Models\Vendor::find($vendorId);
                if ($vendor) {
                    $query->where(function($q) use ($vendor, $vendorId) {
                        $q->where('id', $vendor->user_id) // Vendor owner
                          ->orWhere('vendor_id', $vendorId); // Vendor customers
                    });
                }
                break;
                
            case 'categories':
                // Export categories used by vendor's products
                $products = DB::table('products')->where('vendor_id', $vendorId)->get();
                if ($products->isNotEmpty()) {
                    $categoryIds = [];
                    foreach ($products as $product) {
                        if (!empty($product->product_categories)) {
                            $productCategories = json_decode($product->product_categories, true);
                            if (is_array($productCategories)) {
                                foreach ($productCategories as $category) {
                                    if (isset($category['category_id'])) {
                                        $categoryIds[] = $category['category_id'];
                                    }
                                }
                            }
                        }
                    }
                    
                    $categoryIds = array_unique(array_filter($categoryIds));
                    
                    if (!empty($categoryIds)) {
                        $query->whereIn('id', $categoryIds);
                    } else {
                        // No categories, return empty result
                        $query->whereRaw('1 = 0');
                    }
                } else {
                    // No products, return empty result
                    $query->whereRaw('1 = 0');
                }
                break;
                
            case 'sub_categories':
                // Export subcategories used by vendor's products
                $products = DB::table('products')->where('vendor_id', $vendorId)->get();
                if ($products->isNotEmpty()) {
                    $subcategoryIds = [];
                    foreach ($products as $product) {
                        if (!empty($product->product_categories)) {
                            $productCategories = json_decode($product->product_categories, true);
                            if (is_array($productCategories)) {
                                foreach ($productCategories as $category) {
                                    if (isset($category['subcategory_ids']) && is_array($category['subcategory_ids'])) {
                                        $subcategoryIds = array_merge($subcategoryIds, $category['subcategory_ids']);
                                    }
                                }
                            }
                        }
                    }
                    
                    $subcategoryIds = array_unique(array_filter($subcategoryIds));
                    
                    if (!empty($subcategoryIds)) {
                        $query->whereIn('id', $subcategoryIds);
                    } else {
                        // No subcategories, return empty result
                        $query->whereRaw('1 = 0');
                    }
                } else {
                    // No products, return empty result
                    $query->whereRaw('1 = 0');
                }
                break;
                
            case 'product_images':
                // Export images for vendor's products
                $productIds = DB::table('products')->where('vendor_id', $vendorId)->pluck('id');
                if ($productIds->isNotEmpty()) {
                    $query->whereIn('product_id', $productIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
                
            case 'product_views':
                // Export views for vendor's products
                $productIds = DB::table('products')->where('vendor_id', $vendorId)->pluck('id');
                if ($productIds->isNotEmpty()) {
                    $query->whereIn('product_id', $productIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
                
            case 'shopping_cart_items':
                // Export cart items for vendor's products
                $productIds = DB::table('products')->where('vendor_id', $vendorId)->pluck('id');
                if ($productIds->isNotEmpty()) {
                    $query->whereIn('product_id', $productIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
                
            case 'wishlists':
                // Export wishlist items for vendor's products
                $productIds = DB::table('products')->where('vendor_id', $vendorId)->pluck('id');
                if ($productIds->isNotEmpty()) {
                    $query->whereIn('product_id', $productIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
                
            case 'proforma_invoice_items':
                // Export invoice items for vendor's invoices
                $invoiceIds = DB::table('proforma_invoices')->where('vendor_id', $vendorId)->pluck('id');
                if ($invoiceIds->isNotEmpty()) {
                    $query->whereIn('proforma_invoice_id', $invoiceIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
                
            case 'without_gst_invoice_items':
                // Export invoice items for vendor's invoices
                $invoiceIds = DB::table('without_gst_invoices')->where('vendor_id', $vendorId)->pluck('id');
                if ($invoiceIds->isNotEmpty()) {
                    $query->whereIn('without_gst_invoice_id', $invoiceIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
                
            case 'tasks':
                // Export tasks assigned to vendor
                $query->where('vendor_id', $vendorId);
                break;
                
            case 'activity_logs':
                // Export activity logs for vendor
                $query->where(function($q) use ($vendorId) {
                    $q->where('vendor_id', $vendorId)
                      ->orWhere(function($subQ) use ($vendorId) {
                          $subQ->where('model_type', 'App\\Models\\Vendor')
                               ->where('model_id', $vendorId);
                      });
                });
                break;
        }
        
        return $query;
    }

    /**
     * Split SQL content into individual statements
     */
    private function splitSqlStatements($sql)
    {
        // Remove comments
        $sql = preg_replace('/^--.*$/m', '', $sql);
        $sql = preg_replace('/^#.*$/m', '', $sql);
        
        // Split by semicolon (but not inside quotes)
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (($char === '"' || $char === "'") && ($i === 0 || $sql[$i-1] !== '\\')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }
            
            if ($char === ';' && !$inString) {
                $statements[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }
        
        if (trim($current) !== '') {
            $statements[] = trim($current);
        }
        
        return array_filter($statements);
    }

    /**
     * Get list of previous exports
     */
    private function getPreviousExports()
    {
        $files = Storage::disk('local')->files('exports');
        
        $exports = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $exports[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => Storage::disk('local')->size($file),
                    'date' => Storage::disk('local')->lastModified($file),
                ];
            }
        }
        
        // Sort by date descending
        usort($exports, function($a, $b) {
            return $b['date'] - $a['date'];
        });
        
        return $exports;
    }

    /**
     * Download previous export
     */
    public function downloadExport($filename)
    {
        $filepath = 'exports/' . $filename;
        
        if (!Storage::disk('local')->exists($filepath)) {
            return redirect()->back()->with('error', 'File not found.');
        }
        
        return response()->download(storage_path('app/' . $filepath));
    }

    /**
     * Delete export file
     */
    public function deleteExport($filename)
    {
        $filepath = 'exports/' . $filename;
        
        if (Storage::disk('local')->exists($filepath)) {
            Storage::disk('local')->delete($filepath);
            return redirect()->back()->with('success', 'Export deleted successfully.');
        }
        
        return redirect()->back()->with('error', 'File not found.');
    }
}
