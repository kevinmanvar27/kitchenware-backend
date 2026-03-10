<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Traits\LogsActivity;

class PageController extends Controller
{
    use LogsActivity;
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = Page::orderBy('priority', 'asc')->orderBy('created_at', 'desc')->get();
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:pages,slug',
            'content' => 'nullable|string',
            'active' => 'boolean',
            'priority' => 'integer|min:0',
        ]);

        // Auto-generate slug if not provided
        $slug = $validated['slug'] ?? Str::slug($validated['title']);
        
        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (Page::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $page = Page::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? null,
            'active' => $request->boolean('active', false),
            'priority' => $validated['priority'] ?? 0,
        ]);

        // Log page creation
        $this->logAdminActivity('created', "Created page: {$page->title}", $page);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        return view('admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:pages,slug,' . $page->id,
            'content' => 'nullable|string',
            'active' => 'boolean',
            'priority' => 'integer|min:0',
        ]);

        $page->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'] ?? null,
            'active' => $request->boolean('active', false),
            'priority' => $validated['priority'] ?? 0,
        ]);

        // Log page update
        $this->logAdminActivity('updated', "Updated page: {$page->title}", $page);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page)
    {
        // Capture data before deletion for logging
        $pageTitle = $page->title;
        $pageId = $page->id;
        
        $page->delete();

        // Log page deletion
        $this->logAdminActivity('deleted', "Deleted page: {$pageTitle} (ID: {$pageId})");

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }
}