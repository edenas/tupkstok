<?php

namespace App\Http\Controllers;

use App\Models\PortfolioPost;
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
        $totalPortfolioPosts = PortfolioPost::count();
        $todayVisits = WebsiteVisit::where('visited_at', '>=', now()->startOfDay())->count();

        $popularPages = WebsiteVisit::query()
            ->select('path', DB::raw('MAX(title) as title'), DB::raw('COUNT(*) as visits_count'))
            ->groupBy('path')
            ->orderByDesc('visits_count')
            ->orderBy('path')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'popularPages',
            'todayVisits',
            'totalPages',
            'totalPortfolioPosts',
        ));
    }
}
