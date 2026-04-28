<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ISOLATED = 'isolated';

    public const STATUS_TERMINATED = 'terminated';

    protected $fillable = [
        'tenant_id',
        'package_id',
        'nas_device_id',
        'code',
        'name',
        'phone',
        'email',
        'address',
        'status',
        'pppoe_username',
        'pppoe_password',
        'ip_address',
        'due_day',
        'installed_at',
        'notes',
    ];

    protected $casts = [
        'installed_at' => 'date',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function nasDevice(): BelongsTo
    {
        return $this->belongsTo(NasDevice::class);
    }
}
