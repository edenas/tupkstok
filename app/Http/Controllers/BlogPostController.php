<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogPostController extends Controller
{
    /**
     * Display the public blog listing.
     */
    public function index()
    {
        $blogPosts = BlogPost::query()
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withPath(route('blog'));

        return view('pages.blog', compact('blogPosts'));
    }

    /**
     * Display a public blog post.
     */
    public function show(BlogPost $blogPost)
    {
        return view('pages.blog-show', compact('blogPost'));
    }
}
