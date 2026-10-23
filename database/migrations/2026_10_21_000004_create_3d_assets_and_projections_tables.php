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
        Schema::create('novel_3d_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained('novels')->cascadeOnDelete();
            $table->string('name', 150);
            $table->enum('asset_type', ['character', 'weapon', 'beast', 'artifact', 'environment']);
            $table->string('model_file_url', 255);
            $table->string('thumbnail_url', 255)->nullable();
            $table->decimal('scale_factor', 4, 2)->default(1.00);
            $table->json('camera_init_position')->nullable();
            $table->string('ambient_light_color', 10)->default('#ffffff');
            $table->enum('particle_effect', ['none', 'fireflies', 'snow', 'rain', 'divine_sparks', 'sakura'])->default('none');
            $table->string('sound_ambient_url', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });

        Schema::create('chapter_3d_projections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->foreignId('asset_3d_id')->constrained('novel_3d_assets')->cascadeOnDelete();
            $table->integer('paragraph_index');
            $table->string('trigger_keyword', 100)->nullable();
            $table->string('hologram_title', 150);
            $table->boolean('auto_rotate')->default(true);
            $table->enum('display_mode', ['inline_box', 'split_stage', 'hologram_overlay', 'background_ambience'])->default('split_stage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_3d_projections');
        Schema::dropIfExists('novel_3d_assets');
    }
};
