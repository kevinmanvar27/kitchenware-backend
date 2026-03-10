<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use LogsActivity;

    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }
        
        $categories = Category::with(['subCategories'])
            ->where('vendor_id', $vendor->id)
            ->orderBy('name')
            ->paginate(10);
        
        return view('vendor.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'error' => 'Vendor not found'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            \Log::warning('Category validation failed', ['errors' => $validator->errors()->toArray()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'vendor_id' => $vendor->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ];
        
        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                
                // Ensure the directory exists
                $uploadPath = 'vendor_' . $vendor->id . '/categories';
                if (!Storage::disk('public')->exists($uploadPath)) {
                    Storage::disk('public')->makeDirectory($uploadPath, 0755, true);
                }
                
                // Store the image
                $data['image'] = $image->store($uploadPath, 'public');
                \Log::info('Category image uploaded successfully', [
                    'vendor_id' => $vendor->id,
                    'path' => $data['image'],
                    'original_name' => $image->getClientOriginalName()
                ]);
            } catch (\Exception $e) {
                \Log::error('Category image upload failed: ' . $e->getMessage());
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'error' => 'Image upload failed: ' . $e->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Image upload failed. Please try again.')->withInput();
            }
        }
        
        $category = Category::create($data);
        
        \Log::info('Category created successfully', [
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'has_image' => !empty($category->image)
        ]);

        // Log activity
        $this->logVendorActivity($vendor->id, 'created', "Created category: {$category->name}", $category);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $vendor = $this->getVendor();
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }
        
        $category->load(['subCategories']);
        
        // Ensure image_url is included in response
        $categoryData = $category->toArray();
        
        return response()->json($categoryData);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $vendor = $this->getVendor();
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            \Log::warning('Category validation failed', ['errors' => $validator->errors()->toArray()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Store old values for logging
        $oldValues = $category->only(['name', 'description', 'image', 'is_active']);
        
        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : $category->is_active,
        ];
        
        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                // Delete old image if exists
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
                
                $image = $request->file('image');
                
                // Ensure the directory exists
                $uploadPath = 'vendor_' . $vendor->id . '/categories';
                if (!Storage::disk('public')->exists($uploadPath)) {
                    Storage::disk('public')->makeDirectory($uploadPath, 0755, true);
                }
                
                // Store the image
                $data['image'] = $image->store($uploadPath, 'public');
            } catch (\Exception $e) {
                \Log::error('Category image update failed: ' . $e->getMessage());
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'error' => 'Image upload failed: ' . $e->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Image upload failed. Please try again.')->withInput();
            }
        }
        
        $category->update($data);

        // Log activity with changes
        $newValues = $category->only(['name', 'description', 'image', 'is_active']);
        $this->logVendorActivity($vendor->id, 'updated', "Updated category: {$category->name}", $category, $oldValues, $newValues);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        $vendor = $this->getVendor();
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }
        
        $categoryName = $category->name;
        $categoryId = $category->id;
        
        // Delete category image if exists
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        
        // Delete all subcategories and their images
        foreach ($category->subCategories as $subCategory) {
            if ($subCategory->image && Storage::disk('public')->exists($subCategory->image)) {
                Storage::disk('public')->delete($subCategory->image);
            }
        }
        $category->subCategories()->delete();
        
        $category->delete();

        // Log activity
        $this->logVendorActivity($vendor->id, 'deleted', "Deleted category: {$categoryName} (ID: {$categoryId})");

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Get all categories for AJAX requests.
     */
    public function getAllCategories()
    {
        $vendor = $this->getVendor();
        
        $categories = Category::with('subCategories')
            ->where(function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id)
                      ->orWhereNull('vendor_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return response()->json($categories);
    }

    /**
     * Get subcategories for a category.
     */
    public function getSubCategories(Category $category)
    {
        $vendor = $this->getVendor();
        
        // Allow access to vendor's own categories or global categories
        if ($category->vendor_id !== null && $category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }
        
        $subCategories = $category->subCategories()->with(['category'])->latest()->paginate(10);
        
        return response()->json($subCategories);
    }

    /**
     * Store a new subcategory.
     */
    public function storeSubCategory(Request $request)
    {
        $vendor = $this->getVendor();
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category = Category::find($request->category_id);
        
        // Only allow adding subcategories to vendor's own categories
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }

        $data = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ];
        
        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                
                // Ensure the directory exists
                $uploadPath = 'vendor_' . $vendor->id . '/subcategories';
                if (!Storage::disk('public')->exists($uploadPath)) {
                    Storage::disk('public')->makeDirectory($uploadPath, 0755, true);
                }
                
                // Store the image
                $data['image'] = $image->store($uploadPath, 'public');
            } catch (\Exception $e) {
                \Log::error('Subcategory image upload failed: ' . $e->getMessage());
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'error' => 'Image upload failed: ' . $e->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Image upload failed. Please try again.')->withInput();
            }
        }

        $subCategory = SubCategory::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategory created successfully.',
                'subcategory' => $subCategory
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Subcategory created successfully.');
    }

    /**
     * Show a subcategory.
     */
    public function showSubCategory(SubCategory $subCategory)
    {
        $vendor = $this->getVendor();
        $category = $subCategory->category;
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this subcategory.');
        }
        
        $subCategory->load(['category']);
        
        return response()->json($subCategory);
    }

    /**
     * Update a subcategory.
     */
    public function updateSubCategory(Request $request, SubCategory $subCategory)
    {
        $vendor = $this->getVendor();
        $category = $subCategory->category;
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this subcategory.');
        }
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Store old values for logging
        $oldValues = $subCategory->only(['name', 'description', 'image', 'is_active']);
        
        $data = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : $subCategory->is_active,
        ];
        
        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                // Delete old image if exists
                if ($subCategory->image && Storage::disk('public')->exists($subCategory->image)) {
                    Storage::disk('public')->delete($subCategory->image);
                }
                
                $image = $request->file('image');
                
                // Ensure the directory exists
                $uploadPath = 'vendor_' . $vendor->id . '/subcategories';
                if (!Storage::disk('public')->exists($uploadPath)) {
                    Storage::disk('public')->makeDirectory($uploadPath, 0755, true);
                }
                
                // Store the image
                $data['image'] = $image->store($uploadPath, 'public');
            } catch (\Exception $e) {
                \Log::error('Subcategory image update failed: ' . $e->getMessage());
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'error' => 'Image upload failed: ' . $e->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Image upload failed. Please try again.')->withInput();
            }
        }

        $subCategory->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategory updated successfully.',
                'subcategory' => $subCategory
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Subcategory updated successfully.');
    }

    /**
     * Delete a subcategory.
     */
    public function destroySubCategory(SubCategory $subCategory)
    {
        $vendor = $this->getVendor();
        $category = $subCategory->category;
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this subcategory.');
        }
        
        // Delete subcategory image if exists
        if ($subCategory->image && Storage::disk('public')->exists($subCategory->image)) {
            Storage::disk('public')->delete($subCategory->image);
        }
        
        $subCategory->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategory deleted successfully.'
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Subcategory deleted successfully.');
    }

    /**
     * Create category via AJAX.
     */
    public function createCategory(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Create subcategory via AJAX.
     */
    public function createSubCategory(Request $request)
    {
        return $this->storeSubCategory($request);
    }
}
