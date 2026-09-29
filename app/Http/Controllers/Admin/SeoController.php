<?php

namespace App\Http\Controllers\Admin;

use App\Models\Redirect;

class SeoController extends AdminController
{
    public function edit()
    {
        return view('admin.seo.edit', [
            'definition' => settings()->group('seo'),
            'redirects' => Redirect::orderBy('from_path')->get(),
        ]);
    }
}
