<?php

namespace App\Http\Controllers\API;

use App\Models\Permission;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Permissions",
 *     description="API Endpoints for Permission Management"
 * )
 */
class PermissionController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/permissions",
     *      operationId="getPermissionsList",
     *      tags={"Permissions"},
     *      summary="Get list of permissions",
     *      description="Returns list of permissions with pagination",
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
        $permissions = Permission::paginate(15);
        return $this->sendResponse($permissions, 'Permissions retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/permissions",
     *      operationId="storePermission",
     *      tags={"Permissions"},
     *      summary="Store new permission",
     *      description="Returns permission data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","display_name"},
     *              @OA\Property(property="name", type="string", example="edit_products"),
     *              @OA\Property(property="display_name", type="string", example="Edit Products"),
     *              @OA\Property(property="description", type="string", example="Can edit products"),
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
            'name' => 'required|string|max:255|unique:permissions',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $permission = Permission::create($request->all());

        return $this->sendResponse($permission, 'Permission created successfully.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/permissions/{id}",
     *      operationId="getPermissionById",
     *      tags={"Permissions"},
     *      summary="Get permission information",
     *      description="Returns permission data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Permission id",
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
        $permission = Permission::find($id);

        if (is_null($permission)) {
            return $this->sendError('Permission not found.');
        }

        return $this->sendResponse($permission, 'Permission retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/permissions/{id}",
     *      operationId="updatePermission",
     *      tags={"Permissions"},
     *      summary="Update existing permission",
     *      description="Returns updated permission data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Permission id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","display_name"},
     *              @OA\Property(property="name", type="string", example="edit_products"),
     *              @OA\Property(property="display_name", type="string", example="Edit Products"),
     *              @OA\Property(property="description", type="string", example="Can edit products"),
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
        $permission = Permission::find($id);

        if (is_null($permission)) {
            return $this->sendError('Permission not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,'.$id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $permission->update($request->all());

        return $this->sendResponse($permission, 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/permissions/{id}",
     *      operationId="deletePermission",
     *      tags={"Permissions"},
     *      summary="Delete permission",
     *      description="Deletes a permission",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Permission id",
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
        $permission = Permission::find($id);

        if (is_null($permission)) {
            return $this->sendError('Permission not found.');
        }

        $permission->delete();

        return $this->sendResponse(null, 'Permission deleted successfully.');
    }
}