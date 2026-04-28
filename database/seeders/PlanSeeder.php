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
                'max_nas' => 5,
                'allow_mikrotik' => true,
                'allow_hotspot' => true,
                'allow_voucher' => true,
                'allow_radius' => true,
                'allow_genieacs' => true,
                'allow_whatsapp' => true,
                'allow_pdf_invoice' => true,
                'sort_order' => 0,
            ],
            [
                'code' => Plan::CODE_STARTER,
                'name' => 'Starter',
                'price_idr' => 49000,
                'max_customers' => 50,
                'max_nas' => 1,
                'allow_mikrotik' => true,
                'allow_hotspot' => false,
                'allow_voucher' => false,
                'allow_radius' => false,
                'allow_genieacs' => false,
                'allow_whatsapp' => true,
                'allow_pdf_invoice' => true,
                'sort_order' => 1,
            ],
            [
                'code' => Plan::CODE_PRO,
                'name' => 'Pro',
                'price_idr' => 149000,
                'max_customers' => 250,
                'max_nas' => 3,
                'allow_mikrotik' => true,
                'allow_hotspot' => true,
                'allow_voucher' => true,
                'allow_radius' => true,
                'allow_genieacs' => false,
                'allow_whatsapp' => true,
                'allow_pdf_invoice' => true,
                'sort_order' => 2,
            ],
            [
                'code' => Plan::CODE_BUSINESS,
                'name' => 'Business',
                'price_idr' => 299000,
                'max_customers' => 1000,
                'max_nas' => 10,
                'allow_mikrotik' => true,
                'allow_hotspot' => true,
                'allow_voucher' => true,
                'allow_radius' => true,
                'allow_genieacs' => true,
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
