<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Traits\LogsActivity;

class AttributeController extends Controller
{
    use LogsActivity;
    
    /**
     * Generate a unique slug for an attribute value within an attribute.
     */
    private function generateUniqueValueSlug(ProductAttribute $attribute, string $value, ?int $excludeValueId = null): string
    {
        $baseSlug = \Str::slug($value);
        $slug = $baseSlug;
        $counter = 1;
        
        while (true) {
            $query = $attribute->values()->where('slug', $slug);
            if ($excludeValueId) {
                $query->where('id', '!=', $excludeValueId);
            }
            
            if (!$query->exists()) {
                break;
            }
            
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Check if current user can manage attributes
     */
    private function canManageAttributes()
    {
        $user = Auth::user();
        
        // Vendor owners can always manage attributes
        if ($user->isVendor()) {
            return true;
        }
        
        // Staff can only manage if they have attributes permission
        if ($user->isVendorStaff()) {
            return $user->vendorStaff?->hasPermission('attributes');
        }
        
        return false;
    }

    /**
     * Display a listing of product attributes.
     */
    public function index(Request $request)
    {
        if (!$this->canManageAttributes()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to view attributes.');
        }
        
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }
        
        $query = ProductAttribute::with('values')->where('vendor_id', $vendor->id);
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Validate sort parameters
        $allowedSortFields = ['name', 'slug', 'sort_order', 'created_at', 'is_active'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'sort_order';
        }
        
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        $attributes = $query->paginate(12)->withQueryString();
            
        return view('vendor.attributes.index', compact('attributes'));
    }

    /**
     * Get all attributes as JSON (for AJAX requests)
     */
    public function getAll()
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found.'
            ], 404);
        }
        
        $attributes = ProductAttribute::with('values')
            ->where('vendor_id', $vendor->id)
            ->active()
            ->orderBy('sort_order')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $attributes
        ]);
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create()
    {
        if (!$this->canManageAttributes()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to create attributes.');
        }
        
        return view('vendor.attributes.create');
    }

    /**
     * Store a newly created attribute in storage.
     */
    public function store(Request $request)
    {
        if (!$this->canManageAttributes()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to create attributes.');
        }
        
        $vendor = $this->getVendor();
        
        // Filter out empty values before validation
        $values = array_filter($request->values ?? [], function($value) {
            // Handle both array format (from form) and string format (from AJAX)
            if (is_array($value)) {
                return !empty(trim($value['value'] ?? ''));
            }
            return !empty(trim($value));
        });
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:select,color,button',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            // Generate slug if not provided
            $slug = $request->slug ?: \Str::slug($request->name);
            
            // Ensure unique slug for this vendor
            $originalSlug = $slug;
            $counter = 1;
            while (ProductAttribute::where('vendor_id', $vendor->id)->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            // Create attribute
            $attribute = ProductAttribute::create([
                'vendor_id' => $vendor->id,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'type' => $request->type ?? 'select',
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => (int) filter_var($request->input('is_active', '0'), FILTER_VALIDATE_BOOLEAN),
            ]);
            
            // Create attribute values
            $sortOrder = 0;
            foreach ($values as $value) {
                if (is_array($value)) {
                    // Form submission with array format - use unique slug generation
                    $valueSlug = !empty($value['slug']) ? $value['slug'] : $this->generateUniqueValueSlug($attribute, $value['value']);
                    $attribute->values()->create([
                        'value' => trim($value['value']),
                        'slug' => $valueSlug,
                        'color_code' => $value['color_code'] ?? null,
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]);
                } else {
                    // Simple string value (AJAX) - use unique slug generation
                    $attribute->values()->create([
                        'value' => trim($value),
                        'slug' => $this->generateUniqueValueSlug($attribute, $value),
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]);
                }
            }
            
            // Log attribute creation
            if ($vendor) {
                $this->logVendorActivity($vendor->id, 'created', "Created product attribute: {$attribute->name} with " . count($values) . " values", $attribute);
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attribute created successfully.',
                    'attribute' => $attribute->load('values')
                ]);
            }
            
            return redirect()->route('vendor.attributes.index')->with('success', 'Attribute created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating attribute: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating attribute: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error creating attribute.')->withInput();
        }
    }

    /**
     * Display the specified attribute.
     */
    public function show(ProductAttribute $attribute)
    {
        if (!$this->canManageAttributes()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to view attributes.');
        }
        
        $vendor = $this->getVendor();
        
        // Check if attribute belongs to this vendor
        if ($attribute->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.attributes.index')->with('error', 'You do not have permission to view this attribute.');
        }
        
        $attribute->load('values');
        
        return view('vendor.attributes.show', compact('attribute'));
    }

    /**
     * Show the form for editing the specified attribute.
     */
    public function edit(Request $request, ProductAttribute $attribute)
    {
        if (!$this->canManageAttributes()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to edit attributes.');
        }
        
        $vendor = $this->getVendor();
        
        // Check if attribute belongs to this vendor
        if ($attribute->vendor_id !== $vendor->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->route('vendor.attributes.index')->with('error', 'You do not have permission to edit this attribute.');
        }
        
        $attribute->load('values');
        
        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($attribute);
        }
        
        return view('vendor.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute in storage.
     */
    public function update(Request $request, ProductAttribute $attribute)
    {
        if (!$this->canManageAttributes()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to update attributes.');
        }
        
        $vendor = $this->getVendor();
        
        // Check if attribute belongs to this vendor
        if ($attribute->vendor_id !== $vendor->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->route('vendor.attributes.index')->with('error', 'You do not have permission to update this attribute.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:select,color,button',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            // Generate slug if not provided
            $slug = $request->slug ?: \Str::slug($request->name);
            
            // Ensure unique slug for this vendor (excluding current attribute)
            $originalSlug = $slug;
            $counter = 1;
            while (ProductAttribute::where('vendor_id', $vendor->id)
                    ->where('slug', $slug)
                    ->where('id', '!=', $attribute->id)
                    ->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            // Update attribute
            $attribute->update([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'type' => $request->type ?? $attribute->type,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => (int) filter_var($request->input('is_active', '0'), FILTER_VALIDATE_BOOLEAN),
            ]);
            
            // Log attribute update
            if ($vendor) {
                $this->logVendorActivity($vendor->id, 'updated', "Updated product attribute: {$attribute->name}", $attribute);
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attribute updated successfully.',
                    'attribute' => $attribute->load('values')
                ]);
            }
            
            return redirect()->route('vendor.attributes.edit', $attribute)->with('success', 'Attribute updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating attribute: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating attribute: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error updating attribute.')->withInput();
        }
    }

    /**
     * Remove the specified attribute from storage.
     */
    public function destroy(Request $request, ProductAttribute $attribute)
    {
        if (!$this->canManageAttributes()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to delete attributes.');
        }
        
        $vendor = $this->getVendor();
        
        // Check if attribute belongs to this vendor
        if ($attribute->vendor_id !== $vendor->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->route('vendor.attributes.index')->with('error', 'You do not have permission to delete this attribute.');
        }
        
        // Capture data before deletion for logging
        $attributeName = $attribute->name;
        $attributeId = $attribute->id;
        
        try {
            $attribute->values()->delete();
            $attribute->delete();
            
            // Log attribute deletion
            if ($vendor) {
                $this->logVendorActivity($vendor->id, 'deleted', "Deleted product attribute: {$attributeName} (ID: {$attributeId})");
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attribute deleted successfully.'
                ]);
            }
            
            return redirect()->route('vendor.attributes.index')->with('success', 'Attribute deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting attribute: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting attribute: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error deleting attribute.');
        }
    }

    /**
     * Store a new attribute value.
     */
    public function storeValue(Request $request, ProductAttribute $attribute)
    {
        if (!$this->canManageAttributes()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to manage attribute values.');
        }
        
        $vendor = $this->getVendor();
        
        // Check if attribute belongs to this vendor
        if ($attribute->vendor_id !== $vendor->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to manage this attribute.');
        }
        
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Use unique slug generation to handle duplicate values
            $slug = $request->slug ?: $this->generateUniqueValueSlug($attribute, $request->value);
            
            $attributeValue = $attribute->values()->create([
                'value' => $request->value,
                'slug' => $slug,
                'color_code' => $request->color_code,
                'sort_order' => $request->sort_order ?? 0,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $attributeValue]);
            }
            
            return redirect()->route('vendor.attributes.edit', $attribute)->with('success', 'Value added successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create attribute value: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create attribute value.'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to create attribute value.');
        }
    }

    /**
     * Update an attribute value.
     */
    public function updateValue(Request $request, ProductAttribute $attribute, ProductAttributeValue $value)
    {
        if (!$this->canManageAttributes()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to manage attribute values.');
        }
        
        $vendor = $this->getVendor();
        
        // Check if attribute belongs to this vendor
        if ($attribute->vendor_id !== $vendor->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to manage this attribute.');
        }
        
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Use unique slug generation, excluding current value ID to avoid self-conflict
            $slug = $request->slug ?: $this->generateUniqueValueSlug($attribute, $request->value, $value->id);
            
            $value->update([
                'value' => $request->value,
                'slug' => $slug,
                'color_code' => $request->color_code,
                'sort_order' => $request->sort_order ?? $value->sort_order,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $value]);
            }
            
            return redirect()->route('vendor.attributes.edit', $attribute)->with('success', 'Value updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update attribute value: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update attribute value.'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update attribute value.');
        }
    }

    /**
     * Delete an attribute value.
     */
    public function destroyValue(Request $request, ProductAttribute $attribute, ProductAttributeValue $value)
    {
        if (!$this->canManageAttributes()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to manage attribute values.');
        }
        
        $vendor = $this->getVendor();
        
        // Check if attribute belongs to this vendor
        if ($attribute->vendor_id !== $vendor->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to manage this attribute.');
        }
        
        try {
            $value->delete();
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Value deleted successfully.']);
            }
            
            return redirect()->route('vendor.attributes.edit', $attribute)->with('success', 'Value deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete attribute value: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete attribute value.'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete attribute value.');
        }
    }
}
