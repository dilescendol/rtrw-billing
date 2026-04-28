<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ----- plans: feature flags for tier-based gating -----
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'allow_hotspot')) {
                $table->boolean('allow_hotspot')->default(false)->after('allow_mikrotik');
            }
            if (! Schema::hasColumn('plans', 'allow_voucher')) {
                $table->boolean('allow_voucher')->default(false)->after('allow_hotspot');
            }
            if (! Schema::hasColumn('plans', 'max_nas')) {
                $table->unsignedSmallInteger('max_nas')->default(0)->after('max_customers');
            }
        });

        // ----- packages: bandwidth + pool + type (pppoe/hotspot/static) -----
        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'type')) {
                $table->string('type', 16)->default('pppoe')->after('name');
            }
            if (! Schema::hasColumn('packages', 'download_kbps')) {
                $table->unsignedInteger('download_kbps')->nullable()->after('speed_mbps');
            }
            if (! Schema::hasColumn('packages', 'upload_kbps')) {
                $table->unsignedInteger('upload_kbps')->nullable()->after('download_kbps');
            }
            if (! Schema::hasColumn('packages', 'pool')) {
                $table->string('pool')->nullable()->after('mikrotik_profile');
            }
            if (! Schema::hasColumn('packages', 'duration_minutes')) {
                $table->unsignedInteger('duration_minutes')->nullable()->after('pool');
            }
        });

        // ----- customers: link to nas device + assigned ip -----
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'nas_device_id')) {
                $table->foreignId('nas_device_id')->nullable()->after('package_id');
            }
            if (! Schema::hasColumn('customers', 'ip_address')) {
                $table->string('ip_address', 64)->nullable()->after('pppoe_password');
            }
        });

        // ----- nas_devices: routers (one tenant -> many) -----
        if (! Schema::hasTable('nas_devices')) {
            Schema::create('nas_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('host');
                $table->unsignedSmallInteger('api_port')->default(8728);
                $table->string('api_user');
                $table->text('api_password');
                $table->string('identity')->nullable();
                $table->string('type', 16)->default('mikrotik');
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('last_status', 32)->nullable();
                $table->text('last_status_message')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // ----- hotspot_users -----
        if (! Schema::hasTable('hotspot_users')) {
            Schema::create('hotspot_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('nas_device_id')->nullable();
                $table->foreignId('package_id')->nullable();
                $table->foreignId('customer_id')->nullable();
                $table->string('username')->index();
                $table->string('password');
                $table->string('mac_address', 32)->nullable();
                $table->string('profile')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->string('status', 16)->default('active');
                $table->string('comment')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'username']);
            });
        }

        // ----- vouchers -----
        if (! Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('package_id')->nullable();
                $table->foreignId('nas_device_id')->nullable();
                $table->string('batch_code', 32)->nullable()->index();
                $table->string('code', 32)->index();
                $table->unsignedInteger('price_idr')->default(0);
                $table->string('profile')->nullable();
                $table->unsignedInteger('duration_minutes')->nullable();
                $table->string('status', 16)->default('available'); // available, sold, used, expired
                $table->timestamp('sold_at')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->string('mac_address', 32)->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'code']);
                $table->index(['tenant_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('hotspot_users');
        Schema::dropIfExists('nas_devices');

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
            if (Schema::hasColumn('customers', 'nas_device_id')) {
                $table->dropColumn('nas_device_id');
            }
        });

        Schema::table('packages', function (Blueprint $table) {
            foreach (['type', 'download_kbps', 'upload_kbps', 'pool', 'duration_minutes'] as $col) {
                if (Schema::hasColumn('packages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('plans', function (Blueprint $table) {
            foreach (['allow_hotspot', 'allow_voucher', 'max_nas'] as $col) {
                if (Schema::hasColumn('plans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
