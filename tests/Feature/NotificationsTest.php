<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\GenericNotification;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_persisted_and_marked_read(): void
    {
        $this->seed(PlanSeeder::class);

        $tenant = Tenant::create([
            'name' => 'Demo',
            'slug' => 'demo-notif',
            'business_name' => 'Demo',
            'status' => Tenant::STATUS_TRIAL,
            'trial_ends_at' => now()->addDays(3),
            'invoice_prefix' => 'DEMO',
            'default_due_day' => 5,
        ]);
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin',
            'email' => 'a@test.local',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user->notify(new GenericNotification('Tagihan baru', 'Invoice #1 dibuat', 'receipt', 'info', '/invoices'));

        $this->assertEquals(1, $user->fresh()->unreadNotifications->count());

        $notif = $user->notifications->first();
        $this->actingAs($user)->post(route('notifications.read', $notif->id))->assertRedirect();

        $this->assertEquals(0, $user->fresh()->unreadNotifications->count());
    }

    public function test_landing_renders_with_dark_theme_cookie(): void
    {
        $this->seed(PlanSeeder::class);

        $this->withCookie('theme', 'dark')
            ->get('/')
            ->assertOk()
            ->assertSee('data-bs-theme="dark"', false);
    }
}
