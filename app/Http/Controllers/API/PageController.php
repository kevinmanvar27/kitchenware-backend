<?php

namespace App\Http\Controllers\API;

use App\Models\Page;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Pages",
 *     description="API Endpoints for Page Management"
 * )
 */
class PageController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/pages",
     *      operationId="getPagesList",
     *      tags={"Pages"},
     *      summary="Get list of pages",
     *      description="Returns list of pages with pagination",
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
        $pages = Page::paginate(15);
        return $this->sendResponse($pages, 'Pages retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/pages",
     *      operationId="storePage",
     *      tags={"Pages"},
     *      summary="Store new page",
     *      description="Returns page data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"title","slug","content","is_published"},
     *              @OA\Property(property="title", type="string", example="About Us"),
     *              @OA\Property(property="slug", type="string", example="about-us"),
     *              @OA\Property(property="content", type="string", example="<p>About our company</p>"),
     *              @OA\Property(property="meta_title", type="string", example="About Us"),
     *              @OA\Property(property="meta_description", type="string", example="Learn about our company"),
     *              @OA\Property(property="is_published", type="boolean", example=true),
     *              @OA\Property(property="priority", type="integer", example=1),
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
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'required|boolean',
            'priority' => 'nullable|integer',
        ]);

        $page = Page::create($request->all());

        return $this->sendResponse($page, 'Page created successfully.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/pages/{id}",
     *      operationId="getPageById",
     *      tags={"Pages"},
     *      summary="Get page information",
     *      description="Returns page data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Page id",
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
        $page = Page::find($id);

        if (is_null($page)) {
            return $this->sendError('Page not found.');
        }

        return $this->sendResponse($page, 'Page retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/pages/{id}",
     *      operationId="updatePage",
     *      tags={"Pages"},
     *      summary="Update existing page",
     *      description="Returns updated page data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Page id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"title","slug","content","is_published"},
     *              @OA\Property(property="title", type="string", example="About Us"),
     *              @OA\Property(property="slug", type="string", example="about-us"),
     *              @OA\Property(property="content", type="string", example="<p>About our company</p>"),
     *              @OA\Property(property="meta_title", type="string", example="About Us"),
     *              @OA\Property(property="meta_description", type="string", example="Learn about our company"),
     *              @OA\Property(property="is_published", type="boolean", example=true),
     *              @OA\Property(property="priority", type="integer", example=1),
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
        $page = Page::find($id);

        if (is_null($page)) {
            return $this->sendError('Page not found.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,'.$id,
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'required|boolean',
            'priority' => 'nullable|integer',
        ]);

        $page->update($request->all());

        return $this->sendResponse($page, 'Page updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/pages/{id}",
     *      operationId="deletePage",
     *      tags={"Pages"},
     *      summary="Delete page",
     *      description="Deletes a page",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Page id",
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
        $page = Page::find($id);

        if (is_null($page)) {
            return $this->sendError('Page not found.');
        }

        $page->delete();

        return $this->sendResponse(null, 'Page deleted successfully.');
    }
}