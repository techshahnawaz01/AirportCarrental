<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends AdminController
{
    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $subscribers = Subscriber::query()
            ->when($request->query('q'), fn ($q, $term) => $q->where('email', 'like', "%{$term}%"))
            ->latest()
            ->paginate(config('cms.pagination.admin'))
            ->withQueryString();

        return $this->listing($request, 'admin.subscribers.index', 'admin.subscribers._table', compact('subscribers'));
    }

    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();

        return $this->success('Subscriber removed.');
    }

    public function export()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'subscribed_at']);
            Subscriber::orderBy('id')->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $subscriber) {
                    fputcsv($out, [$subscriber->email, $subscriber->created_at?->toDateTimeString()]);
                }
            });
            fclose($out);
        }, 'subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
