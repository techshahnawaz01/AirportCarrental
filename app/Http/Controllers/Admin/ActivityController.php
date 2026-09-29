<?php

namespace App\Http\Controllers\Admin;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityController extends AdminController
{
    public function __invoke(Request $request)
    {
        $logs = ActivityLog::with('user')->latest('id')->paginate(30);

        return $this->listing($request, 'admin.activity.index', 'admin.activity._list', compact('logs'));
    }
}
