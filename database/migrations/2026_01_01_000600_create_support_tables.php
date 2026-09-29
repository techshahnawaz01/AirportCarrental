<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 500)->unique();
            $table->string('to_url', 500);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60);
            $table->nullableMorphs('subject');
            $table->string('description');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });

        Schema::create('flight_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('airport_iata', 3);
            $table->string('direction', 12); // arrivals | departures
            $table->longText('data');
            $table->timestamp('fetched_at');

            $table->index(['airport_iata', 'direction', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_snapshots');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('redirects');
    }
};
