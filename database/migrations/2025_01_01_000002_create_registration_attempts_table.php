<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email_hash', 128)->index();
            $table->string('ip_hash', 128)->index();
            $table->string('fingerprint_hash', 128)->nullable()->index();
            $table->string('phone_hash', 128)->nullable()->index();
            $table->string('status', 32)->default('attempted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_attempts');
    }
};
