@if ($users->isEmpty())
    <x-ui.empty-state icon="users" title="No users found" />
@else
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th class="hidden md:table-cell">Last sign-in</th><th class="text-right"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">{{ $user->initials() }}</span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium">{{ $user->name }} @if ($user->is(auth()->user()))<span class="text-xs text-fg-muted">(you)</span>@endif</p>
                                    <p class="truncate text-xs text-fg-muted">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td><x-ui.badge :tone="$user->isAdmin() ? 'primary' : 'neutral'">{{ $user->roleLabel() }}</x-ui.badge></td>
                        <td><x-ui.status-toggle :url="route('admin.users.toggle', $user)" :active="$user->is_active" on="Active" off="Inactive" /></td>
                        <td class="hidden text-fg-muted md:table-cell">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-icon" aria-label="Edit"><x-icon name="pencil" class="size-4" /></a>
                                @unless ($user->is(auth()->user()))
                                    <button type="button" class="btn-icon hover:text-red-600" aria-label="Delete" data-action="{{ route('admin.users.destroy', $user) }}" data-method="DELETE" data-confirm="Delete {{ $user->name }}? They will no longer be able to sign in." data-confirm-button="Delete user" data-remove="tr"><x-icon name="trash" class="size-4" /></button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="border-t border-line px-4 py-3">{{ $users->links() }}</div>
@endif
