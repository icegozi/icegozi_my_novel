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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained('novels')->cascadeOnDelete();
            $table->decimal('chapter_number', 8, 2);
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->longText('content');
            $table->unsignedInteger('word_count')->default(0);
            $table->enum('status', ['draft', 'pending_approval', 'published', 'scheduled', 'rejected'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();

            $table->unique(['novel_id', 'chapter_number']);
        });

        Schema::create('reading_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('novel_id')->constrained('novels')->cascadeOnDelete();
            $table->foreignId('last_chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'novel_id']);
        });

        Schema::create('chapter_reading_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->integer('last_read_paragraph')->default(0);
            $table->decimal('scroll_percentage', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['user_id', 'chapter_id']);
        });

        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('novel_id')->constrained('novels')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'novel_id']);
        });

        Schema::create('chapter_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('paragraph_index')->nullable();
            $table->text('content');
            $table->unsignedInteger('likes_count')->default(0);
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_comments');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('chapter_reading_progress');
        Schema::dropIfExists('reading_history');
        Schema::dropIfExists('chapters');
    }
};
