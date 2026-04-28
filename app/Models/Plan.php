<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    public const CODE_TRIAL = 'trial';

    public const CODE_FREE = 'free';

    public const CODE_STARTER = 'starter';

    public const CODE_PRO = 'pro';

    public const CODE_BUSINESS = 'business';

    protected $fillable = [
        'code',
        'name',
        'price_idr',
        'max_customers',
        'max_nas',
        'allow_mikrotik',
        'allow_hotspot',
        'allow_voucher',
        'allow_radius',
        'allow_genieacs',
        'allow_whatsapp',
        'allow_pdf_invoice',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'allow_mikrotik' => 'boolean',
        'allow_hotspot' => 'boolean',
        'allow_voucher' => 'boolean',
        'allow_radius' => 'boolean',
        'allow_genieacs' => 'boolean',
        'allow_whatsapp' => 'boolean',
        'allow_pdf_invoice' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function isPaid(): bool
    {
        return $this->price_idr > 0;
    }

    public function allows(string $feature): bool
    {
        return match ($feature) {
            'mikrotik' => (bool) $this->allow_mikrotik,
            'hotspot' => (bool) $this->allow_hotspot,
            'voucher' => (bool) $this->allow_voucher,
            'radius' => (bool) $this->allow_radius,
            'genieacs' => (bool) $this->allow_genieacs,
            'whatsapp' => (bool) $this->allow_whatsapp,
            'pdf_invoice' => (bool) $this->allow_pdf_invoice,
            default => false,
        };
    }
}
