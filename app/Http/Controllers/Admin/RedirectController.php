<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\RedirectRequest;
use App\Models\Redirect;

class RedirectController extends AdminController
{
    public function store(RedirectRequest $request)
    {
        $redirect = Redirect::create($request->validated());
        $this->log('created', 'Added redirect /'.$redirect->from_path, $redirect);

        return $this->success('Redirect added.', ['html' => view('admin.seo._redirect-row', compact('redirect'))->render()], 201);
    }

    public function update(RedirectRequest $request, Redirect $redirect)
    {
        $redirect->update($request->validated());

        return $this->success('Redirect updated.', ['html' => view('admin.seo._redirect-row', compact('redirect'))->render()]);
    }

    public function destroy(Redirect $redirect)
    {
        $redirect->delete();
        $this->log('deleted', 'Removed redirect /'.$redirect->from_path);

        return $this->success('Redirect removed.');
    }
}
