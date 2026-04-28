<?php

namespace Tests\Feature;

use App\Console\Commands\InvoiceReminderCommand;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappNotifier;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappAutoBillingTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenantOnPlan(string $planCode, bool $whatsappOn = true): Tenant
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
            'fonnte_enabled' => $whatsappOn,
            'fonnte_token' => 'test-token',
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

    private function makeCustomer(Tenant $tenant, string $phone = '081234567890'): Customer
    {
        return Customer::create([
            'tenant_id' => $tenant->id,
            'code' => 'C0001',
            'name' => 'Budi',
            'phone' => $phone,
            'status' => Customer::STATUS_ACTIVE,
            'pppoe_username' => 'budi',
            'pppoe_password' => 'rahasia',
            'due_day' => 5,
        ]);
    }

    private function makeInvoice(Tenant $tenant, Customer $customer, $dueDate, string $status = Invoice::STATUS_UNPAID): Invoice
    {
        return Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'DEMO-001',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'due_date' => $dueDate,
            'amount_idr' => 150000,
            'status' => $status,
        ]);
    }

    public function test_admin_on_pro_plan_can_reach_template_page(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_PRO);
        $admin = $this->makeAdmin($tenant);

        $this->actingAs($admin)->get('/whatsapp/templates')->assertOk()->assertSee('WhatsApp Templates');
    }

    public function test_starter_plan_blocks_whatsapp_routes(): void
    {
        // Patch starter to disallow whatsapp explicitly so we can test gating.
        $tenant = $this->makeTenantOnPlan(Plan::CODE_STARTER);
        $tenant->plan->update(['allow_whatsapp' => false]);
        $admin = $this->makeAdmin($tenant);

        $this->actingAs($admin)->get('/whatsapp/templates')->assertForbidden();
        $this->actingAs($admin)->get('/whatsapp/logs')->assertForbidden();
    }

    public function test_notifier_renders_invoice_placeholders(): void
    {
        $tenant = $this->makeTenantOnPlan(Plan::CODE_PRO);
        $customer = $this->makeCustomer($tenant);
        $invoice = $this->makeInvoice($tenant, $customer, now()->addDays(3));

        $body = (new WhatsappNotifier($tenant))
            ->render(WhatsappTemplate::EVENT_INVOICE_DUE_SOON, $customer, [], $invoice);

        $this->assertNotNull($body);
        $this->assertStringContainsString('Budi', $body);
        $this->assertStringContainsString('DEMO-001', $body);
        $this->assertStringContainsString('150.000', $body);
    }

    public function test_notifier_skips_when_plan_does_not_allow_whatsapp(): void
    {
        Http::fake();
        $tenant = $this->makeTenantOnPlan(Plan::CODE_TRIAL);
        $tenant->plan->update(['allow_whatsapp' => false]);
        $customer = $this->makeCustomer($tenant);

        $ok = (new WhatsappNotifier($tenant))->send(WhatsappTemplate::EVENT_INVOICE_CREATED, $customer);
        $this->assertFalse($ok);
        Http::assertNothingSent();
        $this->assertSame(0, WhatsappLog::count());
    }

    public function test_notifier_dispatches_to_fonnte_and_logs_success(): void
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => true, 'id' => 'x'], 200),
        ]);

        $tenant = $this->makeTenantOnPlan(Plan::CODE_PRO);
        $customer = $this->makeCustomer($tenant);
        $invoice = $this->makeInvoice($tenant, $customer, now()->addDays(3));

        $ok = (new WhatsappNotifier($tenant))->send(WhatsappTemplate::EVENT_INVOICE_DUE_SOON, $customer, [], $invoice);
        $this->assertTrue($ok);

        Http::assertSent(function ($req) {
            $headers = $req->header('Authorization');

            return str_contains($req->url(), 'api.fonnte.com/send')
                && in_array('test-token', $headers, true);
        });

        $log = WhatsappLog::firstOrFail();
        $this->assertTrue($log->success);
        $this->assertSame('6281234567890', $log->phone);
        $this->assertSame(WhatsappTemplate::EVENT_INVOICE_DUE_SOON, $log->event);
    }

    public function test_invoice_remind_command_sends_due_soon_for_h_minus_3(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['ok' => true], 200)]);

        $tenant = $this->makeTenantOnPlan(Plan::CODE_PRO);
        $customer = $this->makeCustomer($tenant);
        $this->makeInvoice($tenant, $customer, now()->addDays(3)->startOfDay());

        $this->artisan(InvoiceReminderCommand::class)->assertSuccessful();

        Http::assertSentCount(1);
        $this->assertSame(WhatsappTemplate::EVENT_INVOICE_DUE_SOON, WhatsappLog::firstOrFail()->event);
    }

    public function test_invoice_remind_command_sends_overdue_for_h_plus_7(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['ok' => true], 200)]);

        $tenant = $this->makeTenantOnPlan(Plan::CODE_PRO);
        $customer = $this->makeCustomer($tenant);
        $this->makeInvoice($tenant, $customer, now()->subDays(7)->startOfDay(), Invoice::STATUS_OVERDUE);

        $this->artisan(InvoiceReminderCommand::class)->assertSuccessful();

        Http::assertSentCount(1);
        $log = WhatsappLog::firstOrFail();
        $this->assertSame(WhatsappTemplate::EVENT_INVOICE_OVERDUE, $log->event);
    }

    public function test_deactivated_template_suppresses_notification(): void
    {
        Http::fake();
        $tenant = $this->makeTenantOnPlan(Plan::CODE_PRO);
        $customer = $this->makeCustomer($tenant);
        $invoice = $this->makeInvoice($tenant, $customer, now()->addDays(3));
        WhatsappTemplate::create([
            'tenant_id' => $tenant->id,
            'event' => WhatsappTemplate::EVENT_INVOICE_DUE_SOON,
            'message' => 'should not be sent',
            'is_active' => false,
        ]);

        $ok = (new WhatsappNotifier($tenant))->send(
            WhatsappTemplate::EVENT_INVOICE_DUE_SOON, $customer, [], $invoice
        );
        $this->assertFalse($ok);
        Http::assertNothingSent();
        $this->assertSame(0, WhatsappLog::count());
    }

    public function test_invoice_remind_command_skips_invoice_already_reminded_today(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['ok' => true], 200)]);
        $tenant = $this->makeTenantOnPlan(Plan::CODE_PRO);
        $customer = $this->makeCustomer($tenant);
        $invoice = $this->makeInvoice($tenant, $customer, now()->addDays(3)->startOfDay());
        $invoice->forceFill(['reminder_count' => 1, 'last_reminder_at' => now()])->save();

        $this->artisan(InvoiceReminderCommand::class)->assertSuccessful();
        Http::assertNothingSent();
    }

    public function test_invoice_remind_command_skips_non_reminder_days(): void
    {
        Http::fake();
        $tenant = $this->makeTenantOnPlan(Plan::CODE_PRO);
        $customer = $this->makeCustomer($tenant);
        // due_date 2 days from now is NOT in {-3, -1}; 2 days overdue is NOT in {1, 3, 7}
        $this->makeInvoice($tenant, $customer, now()->addDays(2)->startOfDay());

        $this->artisan(InvoiceReminderCommand::class)->assertSuccessful();
        Http::assertNothingSent();
    }
}
