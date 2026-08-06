<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\WebsiteVisit;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $totalPages = 6;
        $totalBlogPosts = BlogPost::count();
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $todayVisits = WebsiteVisit::whereBetween('visited_at', [$todayStart, $todayEnd])->count();

        $popularPages = WebsiteVisit::query()
            ->select('path', DB::raw('MAX(title) as title'), DB::raw('COUNT(*) as visits_count'))
            ->whereBetween('visited_at', [$todayStart, $todayEnd])
            ->groupBy('path')
            ->orderByDesc('visits_count')
            ->orderBy('path')
            ->paginate(10, ['*'], 'pages')
            ->withQueryString();

        return view('admin.dashboard', compact(
            'popularPages',
            'todayVisits',
            'totalPages',
            'totalBlogPosts',
        ));
    }
}
