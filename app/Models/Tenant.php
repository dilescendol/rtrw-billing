<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    public const STATUS_TRIAL = 'trial';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'owner_user_id',
        'plan_id',
        'name',
        'slug',
        'status',
        'trial_ends_at',
        'plan_ends_at',
        'suspended_reason',
        'business_name',
        'business_phone',
        'business_address',
        'logo_path',
        'currency',
        'invoice_prefix',
        'default_due_day',
        'pakasir_api_key',
        'pakasir_project',
        'pakasir_signature',
        'pakasir_enabled',
        'mikrotik_host',
        'mikrotik_port',
        'mikrotik_user',
        'mikrotik_password',
        'mikrotik_enabled',
        'fonnte_token',
        'fonnte_enabled',
    ];

    protected $hidden = [
        'pakasir_api_key',
        'pakasir_signature',
        'mikrotik_password',
        'fonnte_token',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'plan_ends_at' => 'datetime',
        'pakasir_enabled' => 'boolean',
        'mikrotik_enabled' => 'boolean',
        'fonnte_enabled' => 'boolean',
        'pakasir_api_key' => 'encrypted',
        'pakasir_signature' => 'encrypted',
        'mikrotik_password' => 'encrypted',
        'fonnte_token' => 'encrypted',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function nasDevices(): HasMany
    {
        return $this->hasMany(NasDevice::class);
    }

    public function hotspotUsers(): HasMany
    {
        return $this->hasMany(HotspotUser::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isUsable(): bool
    {
        if ($this->isSuspended()) {
            return false;
        }
        if ($this->isTrial() && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return false;
        }
        if ($this->isActive() && $this->plan_ends_at && $this->plan_ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function trialDaysLeft(): int
    {
        if (! $this->trial_ends_at) {
            return 0;
        }
        $diff = now()->diffInDays($this->trial_ends_at, false);

        return max(0, (int) ceil($diff));
    }
}
