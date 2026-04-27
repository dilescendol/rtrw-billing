<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders(): void
    {
        $this->seed(PlanSeeder::class);
        $this->get('/')->assertOk()->assertSee('Billing ISP RT/RW Net');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_register_creates_tenant_and_user(): void
    {
        $this->seed(PlanSeeder::class);

        $this->post('/register', [
            'name' => 'Test Owner',
            'business_name' => 'RT 99 Net',
            'email' => 'owner@example.test',
            'phone' => '0812345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'agree' => 1,
        ])->assertRedirect();

        $user = User::where('email', 'owner@example.test')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->tenant_id);

        $tenant = Tenant::find($user->tenant_id);
        $this->assertEquals(Tenant::STATUS_TRIAL, $tenant->status);
        $this->assertNotNull($tenant->trial_ends_at);
    }

    public function test_login_redirects_to_dashboard(): void
    {
        $this->seed(PlanSeeder::class);

        $tenant = Tenant::create([
            'name' => 'Demo',
            'slug' => 'demo',
            'business_name' => 'Demo Net',
            'status' => Tenant::STATUS_TRIAL,
            'trial_ends_at' => now()->addDays(3),
            'invoice_prefix' => 'DEMO',
            'default_due_day' => 5,
        ]);

        $user = User::create([
            'name' => 'Demo Owner',
            'email' => 'demo@test.local',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $tenant->update(['owner_user_id' => $user->id]);

        $this->post('/login', [
            'email' => 'demo@test.local',
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }
}
