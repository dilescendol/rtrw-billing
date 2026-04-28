<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ----- Plan flags -----
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'allow_radius')) {
                $table->boolean('allow_radius')->default(false)->after('allow_voucher');
            }
            if (! Schema::hasColumn('plans', 'allow_genieacs')) {
                $table->boolean('allow_genieacs')->default(false)->after('allow_radius');
            }
        });

        // ----- NAS device shared secret (RADIUS) -----
        Schema::table('nas_devices', function (Blueprint $table) {
            if (! Schema::hasColumn('nas_devices', 'radius_secret')) {
                $table->text('radius_secret')->nullable()->after('api_password');
            }
        });

        // ----- Customer RADIUS metadata (group / cached MAC) -----
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'radius_group')) {
                $table->string('radius_group', 64)->nullable()->after('ip_address');
            }
            if (! Schema::hasColumn('customers', 'last_seen_mac')) {
                $table->string('last_seen_mac', 32)->nullable()->after('radius_group');
            }
        });

        // ----- radius_servers (FreeRADIUS connection) -----
        if (! Schema::hasTable('radius_servers')) {
            Schema::create('radius_servers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('host');
                $table->unsignedSmallInteger('auth_port')->default(1812);
                $table->unsignedSmallInteger('acct_port')->default(1813);
                $table->text('shared_secret')->nullable();
                // SQL backend (FreeRADIUS rlm_sql) — optional but recommended
                $table->string('sql_driver', 16)->default('mysql');
                $table->string('sql_host')->nullable();
                $table->unsignedSmallInteger('sql_port')->nullable();
                $table->string('sql_database')->nullable();
                $table->string('sql_username')->nullable();
                $table->text('sql_password')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('last_status', 32)->nullable();
                $table->text('last_status_message')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // ----- genieacs_servers (NBI endpoint) -----
        if (! Schema::hasTable('genieacs_servers')) {
            Schema::create('genieacs_servers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('nbi_url'); // e.g. http://genieacs.example.com:7557
                $table->string('auth_user')->nullable();
                $table->text('auth_password')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('last_status', 32)->nullable();
                $table->text('last_status_message')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('genieacs_servers');
        Schema::dropIfExists('radius_servers');

        Schema::table('customers', function (Blueprint $table) {
            foreach (['radius_group', 'last_seen_mac'] as $c) {
                if (Schema::hasColumn('customers', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('nas_devices', function (Blueprint $table) {
            if (Schema::hasColumn('nas_devices', 'radius_secret')) {
                $table->dropColumn('radius_secret');
            }
        });

        Schema::table('plans', function (Blueprint $table) {
            foreach (['allow_radius', 'allow_genieacs'] as $c) {
                if (Schema::hasColumn('plans', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
