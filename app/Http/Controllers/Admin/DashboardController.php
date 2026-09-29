<?php

namespace App\Http\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Enquiry;
use App\Models\Media;
use App\Models\Page;
use App\Models\Subscriber;
use App\Models\User;
use App\Services\FlightDataService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DashboardController extends AdminController
{
    public function __invoke(FlightDataService $flights)
    {
        $pageCounts = Page::query()
            ->selectRaw('type, COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as published')
            ->groupBy('type')->get()->keyBy('type');

        $stats = [
            'pages' => (int) $pageCounts->sum('total'),
            'published' => (int) $pageCounts->sum('published'),
            'users' => User::count(),
            'enquiries' => Enquiry::count(),
            'unread_enquiries' => Enquiry::unread()->count(),
            'subscribers' => Subscriber::count(),
            'pending_comments' => Comment::where('is_approved', false)->count(),
            'media' => Media::count(),
        ];
        $stats['submissions'] = $stats['enquiries'] + $stats['subscribers'] + Comment::count();

        $status = [
            ['label' => 'Search engine indexing', 'ok' => settings()->bool('seo.indexing_enabled'), 'text' => settings()->bool('seo.indexing_enabled') ? 'Allowed' : 'Blocked'],
            ['label' => 'Home page', 'ok' => (bool) Page::find(settings('general.home_page_id'))?->isPublished(), 'text' => Page::find(settings('general.home_page_id'))?->title ?? 'Not set'],
            ['label' => 'Public storage link', 'ok' => File::exists(public_path('storage')), 'text' => File::exists(public_path('storage')) ? 'Linked' : 'Run php artisan storage:link'],
            ['label' => 'Debug mode', 'ok' => ! config('app.debug') || app()->isLocal(), 'text' => config('app.debug') ? 'On' : 'Off'],
            ['label' => 'Flight data integration', 'ok' => $flights->enabled(), 'text' => $flights->enabled() ? 'Configured' : 'Not configured', 'optional' => true],
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'pageCounts' => $pageCounts,
            'status' => $status,
            'recentActivity' => ActivityLog::with('user')->latest('id')->limit(8)->get(),
            'recentEnquiries' => Enquiry::latest()->limit(5)->get(),
            'recentPages' => Page::latest('updated_at')->limit(5)->get(['id', 'title', 'type', 'is_active', 'updated_at']),
        ]);
    }

    public function clearCache()
    {
        settings()->flush();
        Artisan::call('optimize:clear');
        $this->log('cache', 'Cleared application cache');

        return $this->success('Cache cleared.');
    }
}
