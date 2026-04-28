<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_OWNER = 'owner';

    public const ROLE_TEKNISI = 'teknisi';

    public const ROLE_KOLEKTOR = 'kolektor';

    public const ROLE_CUSTOMER = 'customer';

    /** Legacy role retained for backward compatibility with the original scaffold. */
    public const ROLE_PLATFORM_ADMIN = 'platform_admin';

    public const TENANT_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_OWNER,
        self::ROLE_TEKNISI,
        self::ROLE_KOLEKTOR,
        self::ROLE_CUSTOMER,
    ];

    public const PLATFORM_ROLES = [
        self::ROLE_SUPERADMIN,
        self::ROLE_PLATFORM_ADMIN,
    ];

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'name',
        'email',
        'email_verified_at',
        'phone',
        'password',
        'role',
        'fingerprint_hash',
        'register_ip_hash',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'fingerprint_hash',
        'register_ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, self::PLATFORM_ROLES, true);
    }

    public function isPlatformAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_OWNER], true);
    }

    public function isOwner(): bool
    {
        return $this->isAdmin();
    }

    public function isTeknisi(): bool
    {
        return $this->role === self::ROLE_TEKNISI;
    }

    public function isKolektor(): bool
    {
        return $this->role === self::ROLE_KOLEKTOR;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_OWNER,
            self::ROLE_TEKNISI,
            self::ROLE_KOLEKTOR,
        ], true);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_SUPERADMIN, self::ROLE_PLATFORM_ADMIN => 'Super Admin',
            self::ROLE_ADMIN, self::ROLE_OWNER => 'Admin / Owner',
            self::ROLE_TEKNISI => 'Teknisi',
            self::ROLE_KOLEKTOR => 'Kolektor',
            self::ROLE_CUSTOMER => 'Pelanggan',
            default => ucfirst((string) $this->role),
        };
    }
}
