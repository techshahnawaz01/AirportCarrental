<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->withoutVite();
    }

    protected function admin(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    protected function editor(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => User::ROLE_EDITOR, 'is_active' => true]);
    }

    /** Headers the admin/front-end JavaScript sends with AJAX requests. */
    protected function ajax(): static
    {
        return $this->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']);
    }
}
