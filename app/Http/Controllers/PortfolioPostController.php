<?php

namespace App\Http\Controllers;

use App\Models\PortfolioPost;

class PortfolioPostController extends Controller
{
    /**
     * Display the public graphics portfolio listing.
     */
    public function index()
    {
        $portfolioPosts = PortfolioPost::query()
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withPath(route('graphics'));

        return view('pages.animations', compact('portfolioPosts'));
    }

    /**
     * Display a public portfolio post.
     */
    public function show(PortfolioPost $portfolioPost)
    {
        return view('pages.animation-show', compact('portfolioPost'));
    }
}
