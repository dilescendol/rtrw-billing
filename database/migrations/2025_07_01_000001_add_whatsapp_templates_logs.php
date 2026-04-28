<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('whatsapp_templates')) {
            Schema::create('whatsapp_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('event', 64); // invoice_created, invoice_due_soon, invoice_overdue, payment_received, customer_isolated, voucher_created
                $table->string('name')->nullable();
                $table->text('message');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'event']);
            });
        }

        if (! Schema::hasTable('whatsapp_logs')) {
            Schema::create('whatsapp_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('event', 64)->nullable();
                $table->string('phone', 32);
                $table->text('message');
                $table->boolean('success')->default(false);
                $table->text('response')->nullable();
                $table->foreignId('customer_id')->nullable();
                $table->foreignId('invoice_id')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'event']);
                $table->index(['tenant_id', 'success']);
            });
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'reminder_count')) {
                $table->unsignedTinyInteger('reminder_count')->default(0)->after('notes');
            }
            if (! Schema::hasColumn('invoices', 'last_reminder_at')) {
                $table->timestamp('last_reminder_at')->nullable()->after('reminder_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            foreach (['reminder_count', 'last_reminder_at'] as $c) {
                if (Schema::hasColumn('invoices', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('whatsapp_templates');
    }
};
