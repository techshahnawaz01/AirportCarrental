<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40)->default('page');
            $table->string('template', 60)->default('default');
            $table->string('title');
            $table->string('slug');
            // Full hierarchical URL path, e.g. "blog/my-post". Empty string never used: the home page is resolved via settings.
            $table->string('path', 500)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->json('data')->nullable();
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('noindex')->default(false);
            $table->boolean('nofollow')->default(false);
            $table->decimal('sitemap_priority', 2, 1)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_comments')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index(['parent_id', 'is_active']);
            $table->index('published_at');
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('question', 500);
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['page_id', 'sort_order']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('email');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('body');
            $table->boolean('is_approved')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['page_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('pages');
    }
};
