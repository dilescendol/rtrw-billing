<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NasDevice extends Model
{
    use BelongsToTenant, HasFactory;

    public const TYPE_MIKROTIK = 'mikrotik';

    public const TYPE_OTHER = 'other';

    public const STATUS_OK = 'ok';

    public const STATUS_FAILED = 'failed';

    public const STATUS_UNKNOWN = 'unknown';

    protected $fillable = [
        'tenant_id',
        'name',
        'host',
        'api_port',
        'api_user',
        'api_password',
        'identity',
        'type',
        'is_default',
        'is_active',
        'last_status',
        'last_status_message',
        'last_seen_at',
    ];

    protected $casts = [
        'api_password' => 'encrypted',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected $hidden = [
        'api_password',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'nas_device_id');
    }

    public function hotspotUsers(): HasMany
    {
        return $this->hasMany(HotspotUser::class, 'nas_device_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'nas_device_id');
    }

    public function statusBadge(): string
    {
        return match ($this->last_status) {
            self::STATUS_OK => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'secondary',
        };
    }
}
