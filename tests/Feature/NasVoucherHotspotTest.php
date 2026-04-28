<?php

namespace Tests\Feature;

use App\Models\NasDevice;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Voucher;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NasVoucherHotspotTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenantOnPlan(string $planCode): Tenant
    {
        $this->seed(PlanSeeder::class);
        $plan = Plan::where('code', $planCode)->firstOrFail();

        return Tenant::create([
            'name' => 'Demo '.$planCode,
            'slug' => 'demo-'.uniqid(),
            'plan_id' => $plan->id,
            'business_name' => 'Demo Net',
            'status' => Tenant::STATUS_TRIAL,
            'trial_ends_at' => now()->addDays(3),
            'invoice_prefix' => 'DEMO',
            'default_due_day' => 5,
        ]);
    }

    private function makeAdmin(Tenant $tenant): User
    {
        return User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@test.local',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_reach_nas_listing_on_trial_plan(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_TRIAL);
        $admin = $this->makeAdmin($tenant);

        $this->actingAs($admin)->get('/nas')->assertOk()->assertSee('NAS / Router');
    }

    public function test_starter_plan_blocks_hotspot_and_voucher_routes(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_STARTER);
        $admin = $this->makeAdmin($tenant);

        // Starter ditolak utk hotspot & voucher
        $this->actingAs($admin)->get('/hotspot')->assertForbidden();
        $this->actingAs($admin)->get('/vouchers')->assertForbidden();
        // Tapi NAS (mikrotik) tetap boleh
        $this->actingAs($admin)->get('/nas')->assertOk();
    }

    public function test_pro_plan_can_create_nas_device_and_generate_vouchers(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_PRO);
        $admin = $this->makeAdmin($tenant);

        $this->actingAs($admin)->post('/nas', [
            'name' => 'Router HQ',
            'host' => '192.168.88.1',
            'api_port' => 8728,
            'api_user' => 'devin',
            'api_password' => 'secret123',
            'type' => 'mikrotik',
            'is_default' => 1,
            'is_active' => 1,
        ])->assertRedirect('/nas');

        $this->assertDatabaseHas('nas_devices', [
            'tenant_id' => $tenant->id,
            'name' => 'Router HQ',
            'host' => '192.168.88.1',
            'is_default' => true,
        ]);

        $this->actingAs($admin)->post('/vouchers', [
            'count' => 3,
            'price_idr' => 5000,
            'profile' => '1jam',
            'duration_minutes' => 60,
            'code_length' => 6,
        ])->assertRedirect();

        $this->assertSame(3, Voucher::where('tenant_id', $tenant->id)->count());

        $vouchers = Voucher::where('tenant_id', $tenant->id)->get();
        $this->assertSame(1, $vouchers->pluck('batch_code')->unique()->count(), 'voucher dari satu request harus satu batch');
        $this->assertCount(3, $vouchers->pluck('code')->unique(), 'kode voucher harus unik');
    }

    public function test_max_nas_limit_enforced(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_STARTER); // max_nas = 1
        $admin = $this->makeAdmin($tenant);
        NasDevice::create([
            'tenant_id' => $tenant->id,
            'name' => 'NAS-1',
            'host' => '10.0.0.1',
            'api_port' => 8728,
            'api_user' => 'admin',
            'api_password' => 'x',
            'type' => 'mikrotik',
        ]);

        $this->actingAs($admin)->post('/nas', [
            'name' => 'NAS-2',
            'host' => '10.0.0.2',
            'api_port' => 8728,
            'api_user' => 'admin',
            'api_password' => 'x',
            'type' => 'mikrotik',
        ])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, NasDevice::where('tenant_id', $tenant->id)->count());
    }
}
