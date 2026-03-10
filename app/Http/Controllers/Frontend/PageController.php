<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a listing of active pages.
     */
    public function index()
    {
        $pages = Page::where('active', true)
            ->orderBy('priority', 'asc')
            ->get();
        
        return view('frontend.pages.index', compact('pages'));
    }

    /**
     * Display the specified page.
     */
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();
        
        return view('frontend.pages.show', compact('page'));
    }
}