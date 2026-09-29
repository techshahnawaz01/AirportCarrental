<?php

namespace App\Http\Controllers\Admin;

use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends AdminController
{
    public function __construct(private readonly MediaService $media) {}

    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        return $this->listing($request, 'admin.media.index', 'admin.media._grid', [
            'items' => Media::search($request->query('q'))->latest('id')->paginate(24)->withQueryString(),
        ]);
    }

    /**
     * JSON listing for the media picker modal.
     */
    public function picker(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $items = Media::search($request->query('q'))->where('mime_type', 'like', 'image/%')->latest('id')->paginate(30);

        return $this->success('Loaded.', [
            'items' => $items->getCollection()->map->toPickerArray(),
            'next_page' => $items->hasMorePages() ? $items->currentPage() + 1 : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['required', 'image', 'mimes:'.implode(',', config('cms.uploads.image_mimes')), 'max:'.config('cms.uploads.max_image_size')],
        ], [], ['files.*' => 'file']);

        $uploaded = collect($request->file('files'))->map(fn ($file) => $this->media->upload($file, null, $request->user()->id));
        $this->log('uploaded', 'Uploaded '.$uploaded->count().' '.str('file')->plural($uploaded->count()));

        return $this->success($uploaded->count().' '.str('file')->plural($uploaded->count()).' uploaded.', [
            'items' => $uploaded->map->toPickerArray(),
        ], 201);
    }

    public function update(Request $request, Media $media)
    {
        $media->update($request->validate(['alt' => ['nullable', 'string', 'max:255']]));

        return $this->success('Details saved.');
    }

    public function destroy(Media $media)
    {
        $usage = Page::where('featured_image_id', $media->id)->orWhere('og_image_id', $media->id)->count()
            + MenuItem::where('image_id', $media->id)->count();

        $this->media->delete($media);
        $this->log('deleted', 'Deleted media "'.$media->original_name.'"');

        return $this->success($usage ? "Deleted. It was removed from {$usage} ".str('place')->plural($usage).'.' : 'File deleted.');
    }
}
