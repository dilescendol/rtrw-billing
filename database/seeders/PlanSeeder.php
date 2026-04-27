<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => Plan::CODE_TRIAL,
                'name' => 'Trial 3 Hari',
                'price_idr' => 0,
                'max_customers' => 9999,
                'allow_mikrotik' => true,
                'allow_whatsapp' => true,
                'allow_pdf_invoice' => true,
                'sort_order' => 0,
            ],
            [
                'code' => Plan::CODE_STARTER,
                'name' => 'Starter',
                'price_idr' => 49000,
                'max_customers' => 50,
                'allow_mikrotik' => true,
                'allow_whatsapp' => true,
                'allow_pdf_invoice' => true,
                'sort_order' => 1,
            ],
            [
                'code' => Plan::CODE_PRO,
                'name' => 'Pro',
                'price_idr' => 149000,
                'max_customers' => 250,
                'allow_mikrotik' => true,
                'allow_whatsapp' => true,
                'allow_pdf_invoice' => true,
                'sort_order' => 2,
            ],
            [
                'code' => Plan::CODE_BUSINESS,
                'name' => 'Business',
                'price_idr' => 299000,
                'max_customers' => 1000,
                'allow_mikrotik' => true,
                'allow_whatsapp' => true,
                'allow_pdf_invoice' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['code' => $p['code']], $p);
        }
    }
}
