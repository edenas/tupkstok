<?php

namespace App\Http\Controllers;

use App\Models\WebsiteVisit;
use Illuminate\Support\Facades\DB;

class AdminStatisticsController extends Controller
{
    /**
     * Display website visit statistics.
     */
    public function index()
    {
        $now = now();

        $summaryCards = [
            [
                'label_key' => 'messages.admin.statistics.summary.total',
                'value' => WebsiteVisit::count(),
            ],
            [
                'label_key' => 'messages.admin.statistics.summary.year',
                'value' => WebsiteVisit::where('visited_at', '>=', $now->copy()->startOfYear())->count(),
            ],
            [
                'label_key' => 'messages.admin.statistics.summary.month',
                'value' => WebsiteVisit::where('visited_at', '>=', $now->copy()->startOfMonth())->count(),
            ],
            [
                'label_key' => 'messages.admin.statistics.summary.week',
                'value' => WebsiteVisit::where('visited_at', '>=', $now->copy()->startOfWeek())->count(),
            ],
            [
                'label_key' => 'messages.admin.statistics.summary.today',
                'value' => WebsiteVisit::where('visited_at', '>=', $now->copy()->startOfDay())->count(),
            ],
        ];

        $allPages = WebsiteVisit::query()
            ->select('path', DB::raw('MAX(title) as title'), DB::raw('COUNT(*) as visits_count'))
            ->groupBy('path')
            ->orderByDesc('visits_count')
            ->orderBy('path')
            ->paginate(10, ['*'], 'pages')
            ->withQueryString();

        return view('admin.statistics', compact('summaryCards', 'allPages'));
    }
}
