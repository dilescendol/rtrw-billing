<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use BelongsToTenant, HasFactory;

    public const TYPE_PPPOE = 'pppoe';

    public const TYPE_HOTSPOT = 'hotspot';

    public const TYPE_STATIC = 'static';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'price_idr',
        'speed_mbps',
        'download_kbps',
        'upload_kbps',
        'mikrotik_profile',
        'pool',
        'duration_minutes',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rateLimit(): ?string
    {
        if (! $this->upload_kbps || ! $this->download_kbps) {
            return null;
        }

        return $this->upload_kbps.'k/'.$this->download_kbps.'k';
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
