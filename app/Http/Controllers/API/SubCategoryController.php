<?php

namespace App\Http\Controllers\API;

use App\Models\SubCategory;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Sub Categories",
 *     description="API Endpoints for Sub Category Management"
 * )
 */
class SubCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/subcategories",
     *      operationId="getSubCategoriesList",
     *      tags={"Sub Categories"},
     *      summary="Get list of sub categories",
     *      description="Returns list of sub categories with pagination",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index()
    {
        $subCategories = SubCategory::with(['category'])->paginate(15);
        return $this->sendResponse($subCategories, 'Sub categories retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/subcategories",
     *      operationId="storeSubCategory",
     *      tags={"Sub Categories"},
     *      summary="Store new sub category",
     *      description="Returns sub category data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","slug","category_id","is_active"},
     *              @OA\Property(property="name", type="string", example="Smartphones"),
     *              @OA\Property(property="slug", type="string", example="smartphones"),
     *              @OA\Property(property="description", type="string", example="Smartphone subcategory"),
     *              @OA\Property(property="category_id", type="integer", example=1),
     *              @OA\Property(property="is_active", type="boolean", example=true),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sub_categories',
            'description' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
            'is_active' => 'required|boolean',
        ]);

        $subCategory = SubCategory::create($request->all());

        return $this->sendResponse($subCategory, 'Sub category created successfully.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/subcategories/{id}",
     *      operationId="getSubCategoryById",
     *      tags={"Sub Categories"},
     *      summary="Get sub category information",
     *      description="Returns sub category data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Sub category id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function show($id)
    {
        $subCategory = SubCategory::with(['category'])->find($id);

        if (is_null($subCategory)) {
            return $this->sendError('Sub category not found.');
        }

        // Fetch products that belong to this subcategory
        $products = Product::where('status', 'published')
            ->get()
            ->filter(function ($product) use ($id) {
                if (empty($product->product_categories)) {
                    return false;
                }
                
                foreach ($product->product_categories as $category) {
                    if (isset($category['subcategory_ids']) && in_array((int)$id, array_map('intval', $category['subcategory_ids']))) {
                        return true;
                    }
                }
                return false;
            })
            ->values();

        // Convert to array and add products
        $subCategoryData = $subCategory->toArray();
        $subCategoryData['products'] = $products;

        return $this->sendResponse($subCategoryData, 'Sub category retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/subcategories/{id}",
     *      operationId="updateSubCategory",
     *      tags={"Sub Categories"},
     *      summary="Update existing sub category",
     *      description="Returns updated sub category data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Sub category id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","slug","category_id","is_active"},
     *              @OA\Property(property="name", type="string", example="Smartphones"),
     *              @OA\Property(property="slug", type="string", example="smartphones"),
     *              @OA\Property(property="description", type="string", example="Smartphone subcategory"),
     *              @OA\Property(property="category_id", type="integer", example=1),
     *              @OA\Property(property="is_active", type="boolean", example=true),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $subCategory = SubCategory::find($id);

        if (is_null($subCategory)) {
            return $this->sendError('Sub category not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sub_categories,slug,'.$id,
            'description' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
            'is_active' => 'required|boolean',
        ]);

        $subCategory->update($request->all());

        return $this->sendResponse($subCategory, 'Sub category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/subcategories/{id}",
     *      operationId="deleteSubCategory",
     *      tags={"Sub Categories"},
     *      summary="Delete sub category",
     *      description="Deletes a sub category",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Sub category id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function destroy($id)
    {
        $subCategory = SubCategory::find($id);

        if (is_null($subCategory)) {
            return $this->sendError('Sub category not found.');
        }

        $subCategory->delete();

        return $this->sendResponse(null, 'Sub category deleted successfully.');
    }
}