<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            return;
        }
        if (User::where('email', 'demo@rtrw.test')->exists()) {
            return;
        }

        $trialPlan = Plan::where('code', Plan::CODE_TRIAL)->first();

        $tenant = Tenant::create([
            'name' => 'RT 03 RW 02 Net Demo',
            'slug' => 'demo-'.Str::random(6),
            'plan_id' => $trialPlan?->id,
            'status' => Tenant::STATUS_TRIAL,
            'trial_ends_at' => now()->addDays(3),
            'business_name' => 'RT 03 RW 02 Net',
            'business_phone' => '081234567890',
            'business_address' => 'Jl. Demo No. 1, Bandung',
            'invoice_prefix' => 'RTRW',
            'default_due_day' => 5,
        ]);

        $owner = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Owner',
            'email' => 'demo@rtrw.test',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $tenant->update(['owner_user_id' => $owner->id]);

        $packages = collect([
            ['name' => 'Paket 5 Mbps', 'price_idr' => 100000, 'speed_mbps' => 5, 'mikrotik_profile' => '5M'],
            ['name' => 'Paket 10 Mbps', 'price_idr' => 150000, 'speed_mbps' => 10, 'mikrotik_profile' => '10M'],
            ['name' => 'Paket 20 Mbps', 'price_idr' => 250000, 'speed_mbps' => 20, 'mikrotik_profile' => '20M'],
        ])->map(fn ($p) => Package::create(array_merge($p, ['tenant_id' => $tenant->id, 'is_active' => true])));

        for ($i = 1; $i <= 8; $i++) {
            $pkg = $packages->random();
            $cust = Customer::create([
                'tenant_id' => $tenant->id,
                'package_id' => $pkg->id,
                'code' => 'C'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'name' => 'Pelanggan '.$i,
                'phone' => '0812000000'.$i,
                'address' => 'Jl. Demo Blok '.chr(64 + $i).' No. '.$i,
                'status' => Customer::STATUS_ACTIVE,
                'pppoe_username' => 'cust'.$i,
                'pppoe_password' => Str::random(8),
                'due_day' => 5,
                'installed_at' => now()->subDays(rand(10, 60)),
            ]);

            // Create one paid invoice last month, one unpaid this month
            $period = CarbonImmutable::now()->subMonth()->startOfMonth();
            Invoice::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $cust->id,
                'package_id' => $pkg->id,
                'invoice_no' => 'RTRW/'.$period->format('Ym').'/'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'period_start' => $period->toDateString(),
                'period_end' => $period->endOfMonth()->toDateString(),
                'due_date' => $period->day(5)->toDateString(),
                'amount_idr' => $pkg->price_idr,
                'status' => Invoice::STATUS_PAID,
                'paid_at' => $period->day(rand(2, 6)),
                'payment_method' => 'transfer',
            ]);

            $period2 = CarbonImmutable::now()->startOfMonth();
            Invoice::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $cust->id,
                'package_id' => $pkg->id,
                'invoice_no' => 'RTRW/'.$period2->format('Ym').'/'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'period_start' => $period2->toDateString(),
                'period_end' => $period2->endOfMonth()->toDateString(),
                'due_date' => $period2->day(5)->toDateString(),
                'amount_idr' => $pkg->price_idr,
                'status' => $i % 3 === 0 ? Invoice::STATUS_OVERDUE : Invoice::STATUS_UNPAID,
            ]);
        }
    }
}
