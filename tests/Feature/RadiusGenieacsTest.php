<?php

namespace Tests\Feature;

use App\Models\GenieacsServer;
use App\Models\Plan;
use App\Models\RadiusServer;
use App\Models\Tenant;
use App\Models\User;
use App\Services\GenieacsService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RadiusGenieacsTest extends TestCase
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

    public function test_admin_on_trial_plan_can_reach_radius_and_genieacs_lists(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_TRIAL);
        $admin = $this->makeAdmin($tenant);

        $this->actingAs($admin)->get('/radius')->assertOk()->assertSee('RADIUS Servers');
        $this->actingAs($admin)->get('/genieacs')->assertOk()->assertSee('GenieACS');
    }

    public function test_starter_plan_blocks_radius_and_genieacs_routes(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_STARTER);
        $admin = $this->makeAdmin($tenant);

        $this->actingAs($admin)->get('/radius')->assertForbidden();
        $this->actingAs($admin)->get('/genieacs')->assertForbidden();
    }

    public function test_radius_server_can_be_created_and_secrets_are_encrypted(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_TRIAL);
        $admin = $this->makeAdmin($tenant);

        $this->actingAs($admin)->post('/radius', [
            'name' => 'FreeRADIUS Pusat',
            'host' => 'radius.example.com',
            'auth_port' => 1812,
            'acct_port' => 1813,
            'shared_secret' => 'super-secret-shared',
            'sql_driver' => 'mysql',
            'sql_host' => 'db.example.com',
            'sql_port' => 3306,
            'sql_database' => 'radius',
            'sql_username' => 'radius',
            'sql_password' => 'sql-secret',
            'is_default' => '1',
            'is_active' => '1',
        ])->assertRedirect('/radius');

        $server = RadiusServer::where('host', 'radius.example.com')->firstOrFail();
        $this->assertSame('super-secret-shared', $server->shared_secret);
        $this->assertSame('sql-secret', $server->sql_password);

        $rawSecret = (string) \DB::table('radius_servers')->where('id', $server->id)->value('shared_secret');
        $this->assertNotSame('super-secret-shared', $rawSecret, 'shared_secret must be persisted encrypted.');
    }

    public function test_genieacs_test_connection_uses_basic_auth_and_marks_status(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_TRIAL);
        $admin = $this->makeAdmin($tenant);

        Http::fake([
            'genieacs.example.com:7557/devices*' => Http::response([
                ['_id' => 'AABBCC-Test'],
            ], 200),
        ]);

        $server = GenieacsServer::create([
            'tenant_id' => $tenant->id,
            'name' => 'NBI utama',
            'nbi_url' => 'http://genieacs.example.com:7557',
            'auth_user' => 'admin',
            'auth_password' => 'pw',
            'is_default' => true,
            'is_active' => true,
        ]);

        $result = (new GenieacsService($server))->testConnection();
        $this->assertTrue($result['ok']);
        $this->assertSame(GenieacsServer::STATUS_OK, $server->fresh()->last_status);

        Http::assertSent(function ($req) {
            $auth = $req->header('Authorization')[0] ?? '';

            return str_contains($req->url(), '/devices')
                && str_starts_with($auth, 'Basic ');
        });
    }

    public function test_genieacs_reboot_posts_task_with_connection_request(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_TRIAL);
        $this->makeAdmin($tenant);

        Http::fake([
            '*/devices/*/tasks*' => Http::response(['_id' => 'task-1'], 200),
        ]);

        $server = GenieacsServer::create([
            'tenant_id' => $tenant->id,
            'name' => 'NBI',
            'nbi_url' => 'http://genieacs.example.com:7557',
            'is_default' => true,
            'is_active' => true,
        ]);

        $ok = (new GenieacsService($server))->reboot('AABBCC-Test');
        $this->assertTrue($ok);

        Http::assertSent(function ($req) {
            return $req->method() === 'POST'
                && str_contains($req->url(), '/devices/AABBCC-Test/tasks')
                && str_contains($req->url(), 'connection_request=1')
                && ($req->data()['name'] ?? null) === 'reboot';
        });
    }
}
