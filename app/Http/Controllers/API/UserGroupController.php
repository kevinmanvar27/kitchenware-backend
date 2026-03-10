<?php

namespace App\Http\Controllers\API;

use App\Models\UserGroup;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User Groups",
 *     description="API Endpoints for User Group Management"
 * )
 */
class UserGroupController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/user-groups",
     *      operationId="getUserGroupsList",
     *      tags={"User Groups"},
     *      summary="Get list of user groups",
     *      description="Returns list of user groups with pagination",
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
        $userGroups = UserGroup::paginate(15);
        return $this->sendResponse($userGroups, 'User groups retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/user-groups",
     *      operationId="storeUserGroup",
     *      tags={"User Groups"},
     *      summary="Store new user group",
     *      description="Returns user group data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", type="string", example="VIP Customers"),
     *              @OA\Property(property="description", type="string", example="VIP customer group"),
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
            'name' => 'required|string|max:255|unique:user_groups',
            'description' => 'nullable|string',
        ]);

        $userGroup = UserGroup::create($request->all());

        return $this->sendResponse($userGroup, 'User group created successfully.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/user-groups/{id}",
     *      operationId="getUserGroupById",
     *      tags={"User Groups"},
     *      summary="Get user group information",
     *      description="Returns user group data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="User group id",
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
        $userGroup = UserGroup::find($id);

        if (is_null($userGroup)) {
            return $this->sendError('User group not found.');
        }

        return $this->sendResponse($userGroup, 'User group retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/user-groups/{id}",
     *      operationId="updateUserGroup",
     *      tags={"User Groups"},
     *      summary="Update existing user group",
     *      description="Returns updated user group data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="User group id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", type="string", example="VIP Customers"),
     *              @OA\Property(property="description", type="string", example="VIP customer group"),
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
        $userGroup = UserGroup::find($id);

        if (is_null($userGroup)) {
            return $this->sendError('User group not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name,'.$id,
            'description' => 'nullable|string',
        ]);

        $userGroup->update($request->all());

        return $this->sendResponse($userGroup, 'User group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/user-groups/{id}",
     *      operationId="deleteUserGroup",
     *      tags={"User Groups"},
     *      summary="Delete user group",
     *      description="Deletes a user group",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="User group id",
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
        $userGroup = UserGroup::find($id);

        if (is_null($userGroup)) {
            return $this->sendError('User group not found.');
        }

        $userGroup->delete();

        return $this->sendResponse(null, 'User group deleted successfully.');
    }
}