<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('invoice_no')->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date');
            $table->unsignedInteger('amount_idr');
            // status: unpaid, paid, overdue, cancelled
            $table->string('status', 32)->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 32)->nullable();
            $table->string('pakasir_order_id')->nullable()->index();
            $table->string('pakasir_payment_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'due_date']);
            $table->unique(['tenant_id', 'customer_id', 'period_start'], 'invoices_tenant_customer_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
