<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotUser extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DISABLED = 'disabled';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'tenant_id',
        'nas_device_id',
        'package_id',
        'customer_id',
        'username',
        'password',
        'mac_address',
        'profile',
        'expires_at',
        'status',
        'comment',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function nasDevice(): BelongsTo
    {
        return $this->belongsTo(NasDevice::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_DISABLED => 'secondary',
            self::STATUS_EXPIRED => 'danger',
            default => 'secondary',
        };
    }
}
