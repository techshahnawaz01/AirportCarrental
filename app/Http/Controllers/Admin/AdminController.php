<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

abstract class AdminController extends Controller
{
    /**
     * Full page on normal requests; only the refreshed table partial for AJAX
     * filtering, searching and pagination.
     */
    protected function listing(Request $request, string $view, string $partial, array $data)
    {
        if ($request->ajax() && $request->boolean('partial')) {
            return $this->success('Loaded.', ['html' => view($partial, $data)->render()]);
        }

        return view($view, $data);
    }

    protected function log(string $action, string $description, $subject = null): void
    {
        app(ActivityLogger::class)->log($action, $description, $subject);
    }
}
