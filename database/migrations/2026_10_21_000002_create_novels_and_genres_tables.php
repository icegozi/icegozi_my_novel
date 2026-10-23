<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('novels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('synopsis');
            $table->string('cover_image', 255)->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'published', 'rejected', 'paused', 'completed', 'takedown'])->default('draft');
            $table->enum('age_rating', ['all', '13_plus', '16_plus', '18_plus'])->default('all');
            $table->enum('copyright_type', ['original', 'authorized_translation', 'public_domain'])->default('original');
            $table->text('copyright_license_notes')->nullable();
            $table->string('copyright_certificate_file', 255)->nullable();
            $table->json('content_warnings')->nullable();
            $table->unsignedBigInteger('total_words')->default(0);
            $table->unsignedInteger('total_chapters')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedInteger('bookmark_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->text('rejection_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('novel_genre', function (Blueprint $table) {
            $table->foreignId('novel_id')->constrained('novels')->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained('genres')->cascadeOnDelete();
            $table->primary(['novel_id', 'genre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novel_genre');
        Schema::dropIfExists('novels');
        Schema::dropIfExists('genres');
    }
};
