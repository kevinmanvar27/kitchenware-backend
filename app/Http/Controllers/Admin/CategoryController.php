<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $this->authorize('viewAny', Category::class);
        
        $categories = Category::latest()->get();
        
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Category::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['name', 'description', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('categories', $filename, 'public');
            $data['image'] = $path;
        }
        
        $category = Category::create($data);
        
        // Log activity
        $this->logAdminActivity('created', "Created category: {$category->name}", $category);
        
        return response()->json(['success' => true, 'category' => $category]);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $this->authorize('view', $category);
        
        return response()->json($category);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Store old values for logging
        $oldValues = $category->only(['name', 'description', 'image', 'is_active']);
        
        $data = $request->only(['name', 'description', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('categories', $filename, 'public');
            $data['image'] = $path;
        }
        
        $category->update($data);
        
        // Log activity with changes
        $newValues = $category->only(['name', 'description', 'image', 'is_active']);
        $this->logAdminActivity('updated', "Updated category: {$category->name}", $category, $oldValues, $newValues);
        
        return response()->json(['success' => true, 'category' => $category]);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        
        $categoryName = $category->name;
        $categoryId = $category->id;
        
        // Delete image if exists
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        
        $category->delete();
        
        // Log activity
        $this->logAdminActivity('deleted', "Deleted category: {$categoryName} (ID: {$categoryId})");
        
        return response()->json(['success' => true]);
    }

    /**
     * Get subcategories for a specific category.
     */
    public function getSubCategories(Category $category)
    {
        $this->authorize('viewAny', SubCategory::class);
        
        $subCategories = $category->subCategories()->with('category')->latest()->paginate(10);
        
        return response()->json($subCategories);
    }

    /**
     * Store a newly created subcategory in storage.
     */
    public function storeSubCategory(Request $request)
    {
        $this->authorize('create', SubCategory::class);
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['category_id', 'name', 'description', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('subcategories', $filename, 'public');
            $data['image'] = $path;
        }
        
        $subCategory = SubCategory::create($data);
        
        return response()->json(['success' => true, 'subcategory' => $subCategory]);
    }

    /**
     * Display the specified subcategory.
     */
    public function showSubCategory(SubCategory $subCategory)
    {
        $this->authorize('view', $subCategory);
        
        $subCategory->load('category');
        
        return response()->json($subCategory);
    }

    /**
     * Update the specified subcategory in storage.
     */
    public function updateSubCategory(Request $request, SubCategory $subCategory)
    {
        $this->authorize('update', $subCategory);
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['category_id', 'name', 'description', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($subCategory->image && Storage::disk('public')->exists($subCategory->image)) {
                Storage::disk('public')->delete($subCategory->image);
            }
            
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('subcategories', $filename, 'public');
            $data['image'] = $path;
        }
        
        $subCategory->update($data);
        
        return response()->json(['success' => true, 'subcategory' => $subCategory]);
    }

    /**
     * Remove the specified subcategory from storage.
     */
    public function destroySubCategory(SubCategory $subCategory)
    {
        $this->authorize('delete', $subCategory);
        
        // Delete image if exists
        if ($subCategory->image && Storage::disk('public')->exists($subCategory->image)) {
            Storage::disk('public')->delete($subCategory->image);
        }
        
        $subCategory->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get all categories for product management.
     */
    public function getAllCategories()
    {
        $this->authorize('viewAny', Category::class);
        
        $categories = Category::with('subCategories')->where('is_active', true)->get();
        
        return response()->json($categories);
    }

    /**
     * Create a new category via AJAX.
     */
    public function createCategory(Request $request)
    {
        $this->authorize('create', Category::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['name', 'description']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = true;
        
        $category = Category::create($data);
        
        return response()->json(['success' => true, 'category' => $category]);
    }

    /**
     * Create a new subcategory via AJAX.
     */
    public function createSubCategory(Request $request)
    {
        $this->authorize('create', SubCategory::class);
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['category_id', 'name', 'description']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = true;
        
        $subCategory = SubCategory::create($data);
        
        // Load the parent category relationship
        $subCategory->load('category');
        
        return response()->json(['success' => true, 'subcategory' => $subCategory]);
    }
}