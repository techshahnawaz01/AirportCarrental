<?php

namespace App\Http\Controllers\Admin;

use App\Models\Menu;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends AdminController
{
    public function index()
    {
        $menus = Menu::withCount('items')->get()->keyBy('location');

        return view('admin.menus.index', [
            'menus' => $menus,
            'locations' => config('cms.menu_locations'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location' => ['required', Rule::in(array_keys(config('cms.menu_locations'))), 'unique:menus,location'],
        ]);

        $menu = Menu::create($data + ['name' => config("cms.menu_locations.{$data['location']}")]);

        return $this->success('Menu created.', ['redirect' => route('admin.menus.edit', $menu)], 201);
    }

    public function edit(Menu $menu)
    {
        $menu->load(['rootItems.children.page:id,title,path', 'rootItems.page:id,title,path', 'rootItems.image', 'rootItems.children.image']);

        return view('admin.menus.edit', [
            'menu' => $menu,
            'pages' => Page::orderBy('path')->get(['id', 'title', 'path']),
            'icons' => config('cms.icons'),
        ]);
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        $this->log('deleted', 'Deleted menu '.$menu->name);

        return $this->success('Menu deleted.', ['redirect' => route('admin.menus.index')]);
    }
}
