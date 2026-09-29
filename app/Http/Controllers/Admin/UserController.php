<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends AdminController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(array_keys(User::ROLES))],
        ]);

        $users = User::query()
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")))
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate(config('cms.pagination.admin'))
            ->withQueryString();

        return $this->listing($request, 'admin.users.index', 'admin.users._table', compact('users', 'filters'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => User::ROLE_EDITOR, 'is_active' => true])]);
    }

    public function store(UserRequest $request)
    {
        $user = User::create($request->validated());
        $this->log('created', 'Created user '.$user->email, $user);

        return $this->success('User created.', ['redirect' => route('admin.users.index')], 201);
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($error = $this->guardSelfAndLastAdmin($request, $user, $data['role'], $data['is_active'])) {
            return $error;
        }

        $user->update($data);
        $this->log('updated', 'Updated user '.$user->email, $user);

        return $this->success('User saved.');
    }

    public function toggle(Request $request, User $user)
    {
        if ($error = $this->guardSelfAndLastAdmin($request, $user, $user->role, ! $user->is_active)) {
            return $error;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $this->log('updated', ($user->is_active ? 'Activated ' : 'Deactivated ').$user->email, $user);

        return $this->success($user->is_active ? 'User activated.' : 'User deactivated.', ['active' => $user->is_active]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($error = $this->guardSelfAndLastAdmin($request, $user, null, false)) {
            return $error;
        }

        $user->delete();
        $this->log('deleted', 'Deleted user '.$user->email);

        return $this->success('User deleted.');
    }

    private function guardSelfAndLastAdmin(Request $request, User $user, ?string $role, bool $active)
    {
        $losesAdmin = $user->isAdmin() && ($role !== User::ROLE_ADMIN || ! $active);

        if ($user->is($request->user()) && $losesAdmin) {
            return $this->failure('You cannot remove your own administrator access.', 422);
        }

        if ($losesAdmin && User::where('role', User::ROLE_ADMIN)->where('is_active', true)->whereKeyNot($user->id)->doesntExist()) {
            return $this->failure('At least one active administrator is required.', 422);
        }

        return null;
    }
}
