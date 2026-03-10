<?php

namespace App\Http\Controllers\API;

use App\Models\UserGroupMember;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User Group Members",
 *     description="API Endpoints for User Group Member Management"
 * )
 */
class UserGroupMemberController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/user-group-members",
     *      operationId="getUserGroupMembersList",
     *      tags={"User Group Members"},
     *      summary="Get list of user group members",
     *      description="Returns list of user group members with pagination",
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
        $userGroupMembers = UserGroupMember::with(['user', 'userGroup'])->paginate(15);
        return $this->sendResponse($userGroupMembers, 'User group members retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/user-group-members",
     *      operationId="storeUserGroupMember",
     *      tags={"User Group Members"},
     *      summary="Store new user group member",
     *      description="Returns user group member data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"user_id","user_group_id"},
     *              @OA\Property(property="user_id", type="integer", example=1),
     *              @OA\Property(property="user_group_id", type="integer", example=1),
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
            'user_id' => 'required|integer|exists:users,id',
            'user_group_id' => 'required|integer|exists:user_groups,id',
        ]);

        $userGroupMember = UserGroupMember::create($request->all());

        return $this->sendResponse($userGroupMember, 'User group member created successfully.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/user-group-members/{id}",
     *      operationId="getUserGroupMemberById",
     *      tags={"User Group Members"},
     *      summary="Get user group member information",
     *      description="Returns user group member data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="User group member id",
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
        $userGroupMember = UserGroupMember::with(['user', 'userGroup'])->find($id);

        if (is_null($userGroupMember)) {
            return $this->sendError('User group member not found.');
        }

        return $this->sendResponse($userGroupMember, 'User group member retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/user-group-members/{id}",
     *      operationId="updateUserGroupMember",
     *      tags={"User Group Members"},
     *      summary="Update existing user group member",
     *      description="Returns updated user group member data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="User group member id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"user_id","user_group_id"},
     *              @OA\Property(property="user_id", type="integer", example=1),
     *              @OA\Property(property="user_group_id", type="integer", example=1),
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
        $userGroupMember = UserGroupMember::find($id);

        if (is_null($userGroupMember)) {
            return $this->sendError('User group member not found.');
        }

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'user_group_id' => 'required|integer|exists:user_groups,id',
        ]);

        $userGroupMember->update($request->all());

        return $this->sendResponse($userGroupMember, 'User group member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/user-group-members/{id}",
     *      operationId="deleteUserGroupMember",
     *      tags={"User Group Members"},
     *      summary="Delete user group member",
     *      description="Deletes a user group member",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="User group member id",
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
        $userGroupMember = UserGroupMember::find($id);

        if (is_null($userGroupMember)) {
            return $this->sendError('User group member not found.');
        }

        $userGroupMember->delete();

        return $this->sendResponse(null, 'User group member deleted successfully.');
    }
}