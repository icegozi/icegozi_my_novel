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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_email', 191);
            $table->string('reportable_type', 50);
            $table->unsignedBigInteger('reportable_id');
            $table->enum('violation_category', [
                'copyright_infringement',
                'violates_national_security',
                'pornography',
                'violence_hate_speech',
                'defamation',
            ]);
            $table->json('evidence_urls')->nullable();
            $table->text('description');
            $table->enum('status', [
                'received',
                'under_review',
                'temporary_takedown',
                'dismissed',
                'resolved',
            ])->default('received');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('counter_notice_text')->nullable();
            $table->json('counter_notice_evidence_urls')->nullable();
            $table->timestamp('counter_submitted_at')->nullable();
            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('target_type', 50);
            $table->unsignedBigInteger('target_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['target_type', 'target_id']);
            $table->index(['ip_address', 'created_at']);
        });

        Schema::create('content_filters', function (Blueprint $table) {
            $table->id();
            $table->string('pattern', 255);
            $table->enum('category', ['politics', 'vulgarity', 'hate_speech', 'fraud']);
            $table->enum('severity', ['flag_for_review', 'auto_block'])->default('flag_for_review');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_filters');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('reports');
    }
};
