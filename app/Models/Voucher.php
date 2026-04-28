<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Voucher extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_SOLD = 'sold';

    public const STATUS_USED = 'used';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'tenant_id',
        'package_id',
        'nas_device_id',
        'batch_code',
        'code',
        'price_idr',
        'profile',
        'duration_minutes',
        'status',
        'sold_at',
        'used_at',
        'expires_at',
        'mac_address',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function nasDevice(): BelongsTo
    {
        return $this->belongsTo(NasDevice::class);
    }

    public static function generateCode(int $length = 8): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return collect(range(1, $length))
            ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
            ->implode('');
    }

    public static function generateBatchCode(): string
    {
        return strtoupper(Str::random(6));
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'secondary',
            self::STATUS_SOLD => 'info',
            self::STATUS_USED => 'success',
            self::STATUS_EXPIRED => 'warning',
            default => 'secondary',
        };
    }
}
