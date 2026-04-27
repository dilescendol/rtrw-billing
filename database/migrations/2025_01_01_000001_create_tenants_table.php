<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->nullable()->index();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();

            // status: trial, active, suspended
            $table->string('status', 32)->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('plan_ends_at')->nullable();
            $table->string('suspended_reason')->nullable();

            // Branding / business
            $table->string('business_name')->nullable();
            $table->string('business_phone', 32)->nullable();
            $table->text('business_address')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('currency', 8)->default('IDR');
            $table->string('invoice_prefix', 16)->default('INV');
            $table->unsignedTinyInteger('default_due_day')->default(5);

            // Owner-level Pakasir creds (for end-customer billing) — encrypted at rest
            $table->text('pakasir_api_key')->nullable();
            $table->string('pakasir_project')->nullable();
            $table->text('pakasir_signature')->nullable();
            $table->boolean('pakasir_enabled')->default(false);

            // MikroTik creds
            $table->string('mikrotik_host')->nullable();
            $table->unsignedInteger('mikrotik_port')->default(8728);
            $table->string('mikrotik_user')->nullable();
            $table->text('mikrotik_password')->nullable();
            $table->boolean('mikrotik_enabled')->default(false);

            // WhatsApp (Fonnte)
            $table->text('fonnte_token')->nullable();
            $table->boolean('fonnte_enabled')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
