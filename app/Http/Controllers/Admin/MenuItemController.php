<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuItemController extends AdminController
{
    public function store(MenuItemRequest $request, Menu $menu)
    {
        $item = $menu->items()->create($request->validated() + [
            'sort_order' => (int) $menu->items()->where('parent_id', $request->input('parent_id'))->max('sort_order') + 1,
        ]);
        $this->log('created', 'Added "'.$item->label.'" to '.$menu->name, $menu);

        return $this->success('Menu item added.', ['reload' => true], 201);
    }

    public function update(MenuItemRequest $request, Menu $menu, MenuItem $item)
    {
        $data = $request->validated();
        if ((int) ($data['parent_id'] ?? 0) === $item->id || ($item->children()->exists() && ! empty($data['parent_id']))) {
            return $this->failure('Please fix the errors.', 422, ['parent_id' => ['An item with children must stay at the top level.']]);
        }

        $item->update($data);
        $this->log('updated', 'Updated menu item "'.$item->label.'"', $menu);

        return $this->success('Menu item saved.', ['reload' => true]);
    }

    public function destroy(Menu $menu, MenuItem $item)
    {
        $item->delete();
        $this->log('deleted', 'Removed "'.$item->label.'" from '.$menu->name, $menu);

        return $this->success('Menu item removed.');
    }

    /**
     * Receives [{id, parent_id}] in display order.
     */
    public function reorder(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.parent_id' => ['nullable', 'integer'],
        ]);

        $ids = $menu->items()->pluck('id')->all();

        DB::transaction(function () use ($data, $ids, $menu) {
            foreach ($data['items'] as $index => $row) {
                if (in_array($row['id'], $ids, true) && (empty($row['parent_id']) || in_array($row['parent_id'], $ids, true))) {
                    MenuItem::whereKey($row['id'])->where('menu_id', $menu->id)
                        ->update(['sort_order' => $index, 'parent_id' => $row['parent_id'] ?: null]);
                }
            }
        });
        app(MenuService::class)->flush();

        return $this->success('Order saved.');
    }
}
