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
        'allow_mikrotik',
        'allow_whatsapp',
        'allow_pdf_invoice',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'allow_mikrotik' => 'boolean',
        'allow_whatsapp' => 'boolean',
        'allow_pdf_invoice' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function isPaid(): bool
    {
        return $this->price_idr > 0;
    }
}
