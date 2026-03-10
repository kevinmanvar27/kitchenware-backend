<?php

namespace App\Http\Controllers\API;

use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Settings",
 *     description="API Endpoints for Setting Management"
 * )
 */
class SettingController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/settings",
     *      operationId="getSettingsList",
     *      tags={"Settings"},
     *      summary="Get list of settings",
     *      description="Returns list of settings with pagination",
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
        $settings = Setting::paginate(15);
        return $this->sendResponse($settings, 'Settings retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/settings",
     *      operationId="storeSetting",
     *      tags={"Settings"},
     *      summary="Store new setting",
     *      description="Returns setting data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"key","value"},
     *              @OA\Property(property="key", type="string", example="site_name"),
     *              @OA\Property(property="value", type="string", example="My Store"),
     *              @OA\Property(property="description", type="string", example="Site name setting"),
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
            'key' => 'required|string|max:255|unique:settings',
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $setting = Setting::create($request->all());

        return $this->sendResponse($setting, 'Setting created successfully.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/settings/{id}",
     *      operationId="getSettingById",
     *      tags={"Settings"},
     *      summary="Get setting information",
     *      description="Returns setting data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Setting id",
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
        $setting = Setting::find($id);

        if (is_null($setting)) {
            return $this->sendError('Setting not found.');
        }

        return $this->sendResponse($setting, 'Setting retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/settings/{id}",
     *      operationId="updateSetting",
     *      tags={"Settings"},
     *      summary="Update existing setting",
     *      description="Returns updated setting data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Setting id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"key","value"},
     *              @OA\Property(property="key", type="string", example="site_name"),
     *              @OA\Property(property="value", type="string", example="My Store"),
     *              @OA\Property(property="description", type="string", example="Site name setting"),
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
        $setting = Setting::find($id);

        if (is_null($setting)) {
            return $this->sendError('Setting not found.');
        }

        $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,'.$id,
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $setting->update($request->all());

        return $this->sendResponse($setting, 'Setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/settings/{id}",
     *      operationId="deleteSetting",
     *      tags={"Settings"},
     *      summary="Delete setting",
     *      description="Deletes a setting",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Setting id",
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
        $setting = Setting::find($id);

        if (is_null($setting)) {
            return $this->sendError('Setting not found.');
        }

        $setting->delete();

        return $this->sendResponse(null, 'Setting deleted successfully.');
    }
}