<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Public author profiles (WordPress-style): a URL slug, photo, job title, bio and social links.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug', 140)->nullable()->unique()->after('name');
            $table->foreignId('avatar_id')->nullable()->after('slug')->constrained('media')->nullOnDelete();
            $table->string('job_title', 120)->nullable()->after('avatar_id');
            $table->text('bio')->nullable()->after('job_title');
            $table->json('social')->nullable()->after('bio');
        });

        // Existing users get a unique slug from their name (new users get one when saved).
        $taken = [];
        foreach (DB::table('users')->orderBy('id')->get(['id', 'name']) as $user) {
            $base = Str::slug($user->name) ?: 'author';
            $slug = $base;
            for ($i = 2; in_array($slug, $taken, true); $i++) {
                $slug = $base.'-'.$i;
            }
            $taken[] = $slug;
            DB::table('users')->where('id', $user->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('avatar_id');
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'job_title', 'bio', 'social']);
        });
    }
};
