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
        Schema::table('novels', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('meta_title', 255)->nullable()->after('status');
            $table->string('meta_description', 500)->nullable()->after('meta_title');
            $table->string('canonical_url', 500)->nullable()->after('meta_description');
            $table->string('og_image', 255)->nullable()->after('canonical_url');
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('novels', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['meta_title', 'meta_description', 'canonical_url', 'og_image']);
        });
    }
};
