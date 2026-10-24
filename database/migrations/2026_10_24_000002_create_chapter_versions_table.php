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
        Schema::create('chapter_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->unsignedBigInteger('revision')->default(1);
            $table->string('title', 255);
            $table->longText('content');
            $table->unsignedInteger('word_count')->default(0);
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_summary', 255)->nullable();
            $table->timestamps();

            $table->index(['chapter_id', 'revision']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_versions');
    }
};
