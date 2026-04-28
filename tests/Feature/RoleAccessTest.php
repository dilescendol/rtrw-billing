<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Demo',
            'slug' => 'demo-'.uniqid(),
            'business_name' => 'Demo Net',
            'status' => Tenant::STATUS_TRIAL,
            'trial_ends_at' => now()->addDays(3),
            'invoice_prefix' => 'DEMO',
            'default_due_day' => 5,
        ]);
    }

    private function makeUser(Tenant $tenant, string $role, ?int $customerId = null): User
    {
        return User::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customerId,
            'name' => ucfirst($role).' User',
            'email' => $role.'-'.uniqid().'@test.local',
            'password' => bcrypt('password'),
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_role_can_reach_dashboard(): void
    {
        $this->seed(PlanSeeder::class);
        $tenant = $this->makeTenant();
        $admin = $this->makeUser($tenant, User::ROLE_ADMIN);

        $this->actingAs($admin)->get('/dashboard')->assertOk();
    }

    public function test_teknisi_role_can_reach_dashboard_but_not_settings(): void
    {
        $this->seed(PlanSeeder::class);
        $tenant = $this->makeTenant();
        $teknisi = $this->makeUser($tenant, User::ROLE_TEKNISI);

        $this->actingAs($teknisi)->get('/dashboard')->assertOk();
        $this->actingAs($teknisi)->get('/settings')->assertForbidden();
    }

    public function test_customer_role_is_redirected_away_from_dashboard_to_portal(): void
    {
        $this->seed(PlanSeeder::class);
        $tenant = $this->makeTenant();
        $package = Package::create([
            'tenant_id' => $tenant->id,
            'name' => 'Basic',
            'price_idr' => 100000,
            'speed_mbps' => 5,
            'is_active' => true,
        ]);
        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'code' => 'C0001',
            'name' => 'Pelanggan A',
            'phone' => '0812000000',
            'address' => 'Demo',
            'status' => Customer::STATUS_ACTIVE,
            'pppoe_username' => 'cust1',
            'pppoe_password' => 'secret123',
            'due_day' => 5,
        ]);
        $portalUser = $this->makeUser($tenant, User::ROLE_CUSTOMER, $customer->id);

        $this->actingAs($portalUser)->get('/portal')->assertOk();
        $this->actingAs($portalUser)->get('/dashboard')->assertForbidden();
    }

    public function test_superadmin_can_reach_tenant_listing(): void
    {
        $this->seed(PlanSeeder::class);
        $super = User::create([
            'name' => 'Super',
            'email' => 'super@test.local',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SUPERADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($super)->get('/superadmin/tenants')->assertOk();
        $this->actingAs($super)->get('/superadmin/plans')->assertOk();
    }

    public function test_admin_cannot_access_super_admin(): void
    {
        $this->seed(PlanSeeder::class);
        $tenant = $this->makeTenant();
        $admin = $this->makeUser($tenant, User::ROLE_ADMIN);

        $this->actingAs($admin)->get('/superadmin/tenants')->assertForbidden();
    }

    public function test_login_redirects_customer_to_portal(): void
    {
        $this->seed(PlanSeeder::class);
        $tenant = $this->makeTenant();
        $package = Package::create([
            'tenant_id' => $tenant->id,
            'name' => 'Basic',
            'price_idr' => 100000,
            'speed_mbps' => 5,
            'is_active' => true,
        ]);
        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'code' => 'C0002',
            'name' => 'Pelanggan B',
            'phone' => '0812000001',
            'address' => 'Demo',
            'status' => Customer::STATUS_ACTIVE,
            'pppoe_username' => 'cust2',
            'pppoe_password' => 'secret123',
            'due_day' => 5,
        ]);
        $portalUser = User::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'name' => 'Pelanggan B',
            'email' => 'pelanggan-b@test.local',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
            'email_verified_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $portalUser->email,
            'password' => 'password',
        ])->assertRedirect('/portal');
    }
}
