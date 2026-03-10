<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Traits\LogsActivity;

class ProductAttributeController extends Controller
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
     * Display a listing of the attributes.
     */
    public function index()
    {
        // Check if user has permission to view products (attributes are part of product management)
        if (!auth()->user()->hasPermission('viewAny_product')) {
            abort(403, 'Unauthorized action.');
        }
        
        $attributes = ProductAttribute::with(['values', 'vendor'])->orderBy('sort_order')->paginate(20);
        
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create()
    {
        // Check if user has permission to create products
        if (!auth()->user()->hasPermission('create_product')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('admin.attributes.create');
    }

    /**
     * Store a newly created attribute in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to create products
        if (!auth()->user()->hasPermission('create_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        // Filter out empty values before validation
        $values = array_filter($request->values ?? [], function($value) {
            // Handle both array format (from form) and string format (from AJAX)
            if (is_array($value)) {
                return !empty(trim($value['value'] ?? ''));
            }
            return !empty(trim($value));
        });
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:product_attributes,name',
            'slug' => 'nullable|string|max:255|unique:product_attributes,slug',
            'description' => 'nullable|string',
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
            
            // Create attribute
            $attribute = ProductAttribute::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
            ]);
            
            // Create attribute values
            $sortOrder = 0;
            foreach ($values as $value) {
                if (is_array($value)) {
                    // Form submission with array format
                    $valueSlug = !empty($value['slug']) ? $value['slug'] : $this->generateUniqueValueSlug($attribute, $value['value']);
                    $attribute->values()->create([
                        'value' => trim($value['value']),
                        'slug' => $valueSlug,
                        'color_code' => $value['color_code'] ?? null,
                        'sort_order' => $sortOrder++,
                    ]);
                } else {
                    // Simple string value (AJAX)
                    $attribute->values()->create([
                        'value' => trim($value),
                        'slug' => $this->generateUniqueValueSlug($attribute, $value),
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
            
            // Log attribute creation
            $this->logAdminActivity('created', "Created product attribute: {$attribute->name} with " . count($values) . " values", $attribute);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attribute created successfully.',
                    'attribute' => $attribute->load('values')
                ]);
            }
            
            return redirect()->route('admin.attributes.index')->with('success', 'Attribute created successfully.');
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
     * Show the form for editing the specified attribute.
     */
    public function edit(Request $request, ProductAttribute $attribute)
    {
        // Check if user has permission to update products
        if (!auth()->user()->hasPermission('update_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        $attribute->load('values');
        
        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($attribute);
        }
        
        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute in storage.
     */
    public function update(Request $request, ProductAttribute $attribute)
    {
        // Check if user has permission to update products
        if (!auth()->user()->hasPermission('update_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:product_attributes,name,' . $attribute->id,
            'slug' => 'nullable|string|max:255|unique:product_attributes,slug,' . $attribute->id,
            'description' => 'nullable|string',
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
            
            // Update attribute
            $attribute->update([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : $attribute->is_active,
            ]);
            
            // Log attribute update
            $this->logAdminActivity('updated', "Updated product attribute: {$attribute->name}", $attribute);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attribute updated successfully.',
                    'attribute' => $attribute->load('values')
                ]);
            }
            
            return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Attribute updated successfully.');
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
        // Check if user has permission to delete products
        if (!auth()->user()->hasPermission('delete_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        // Capture data before deletion for logging
        $attributeName = $attribute->name;
        $attributeId = $attribute->id;
        
        try {
            $attribute->values()->delete();
            $attribute->delete();
            
            // Log attribute deletion
            $this->logAdminActivity('deleted', "Deleted product attribute: {$attributeName} (ID: {$attributeId})");
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attribute deleted successfully.'
                ]);
            }
            
            return redirect()->route('admin.attributes.index')->with('success', 'Attribute deleted successfully.');
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
        // Check if user has permission to update products
        if (!auth()->user()->hasPermission('update_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to manage attribute values.');
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
            
            return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Value added successfully.');
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
        // Check if user has permission to update products
        if (!auth()->user()->hasPermission('update_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to manage attribute values.');
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
            
            return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Value updated successfully.');
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
        // Check if user has permission to delete products
        if (!auth()->user()->hasPermission('delete_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to manage attribute values.');
        }
        
        try {
            $value->delete();
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Value deleted successfully.']);
            }
            
            return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Value deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete attribute value: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete attribute value.'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete attribute value.');
        }
    }

    /**
     * Get all attributes with their values (for AJAX)
     */
    public function getAttributes()
    {
        // Check if user has permission to view products
        if (!auth()->user()->hasPermission('viewAny_product')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        
        $attributes = ProductAttribute::with('values')->active()->orderBy('sort_order')->get();
        
        return response()->json(['success' => true, 'data' => $attributes]);
    }
}
