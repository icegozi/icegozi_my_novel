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
        Schema::create('author_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('pen_name', 100);
            $table->string('legal_full_name', 150);
            $table->text('identity_number_encrypted');
            $table->string('identity_hash', 64)->unique();
            $table->date('identity_issued_date')->nullable();
            $table->string('identity_front_image', 255)->nullable();
            $table->string('identity_back_image', 255)->nullable();
            $table->string('tax_code', 30)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('legal_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('policy_type', ['terms_of_service', 'author_copyright_agreement', 'privacy_policy']);
            $table->string('policy_version', 20);
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamp('signed_at');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_consents');
        Schema::dropIfExists('author_profiles');
    }
};
