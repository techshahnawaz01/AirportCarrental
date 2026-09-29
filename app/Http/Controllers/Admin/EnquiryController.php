<?php

namespace App\Http\Controllers\Admin;

use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnquiryController extends AdminController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['unread', 'read'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $enquiries = Enquiry::search($filters['q'] ?? null)
            ->when(($filters['status'] ?? null) === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when(($filters['status'] ?? null) === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->latest()
            ->paginate(config('cms.pagination.admin'))
            ->withQueryString();

        return $this->listing($request, 'admin.enquiries.index', 'admin.enquiries._table', compact('enquiries', 'filters'));
    }

    public function show(Enquiry $enquiry)
    {
        if (! $enquiry->read_at) {
            $enquiry->update(['read_at' => now()]);
        }

        return view('admin.enquiries.show', compact('enquiry'));
    }

    public function toggleRead(Enquiry $enquiry)
    {
        $enquiry->update(['read_at' => $enquiry->read_at ? null : now()]);

        return $this->success($enquiry->read_at ? 'Marked as read.' : 'Marked as unread.', ['active' => (bool) $enquiry->read_at]);
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();
        $this->log('deleted', 'Deleted enquiry from '.$enquiry->name);

        return $this->success('Enquiry deleted.', ['redirect' => request()->boolean('redirect') ? route('admin.enquiries.index') : null]);
    }
}
