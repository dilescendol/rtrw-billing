<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiusServer extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_OK = 'ok';

    public const STATUS_FAILED = 'failed';

    public const STATUS_UNKNOWN = 'unknown';

    protected $fillable = [
        'tenant_id',
        'name',
        'host',
        'auth_port',
        'acct_port',
        'shared_secret',
        'sql_driver',
        'sql_host',
        'sql_port',
        'sql_database',
        'sql_username',
        'sql_password',
        'is_default',
        'is_active',
        'last_status',
        'last_status_message',
        'last_seen_at',
    ];

    protected $casts = [
        'shared_secret' => 'encrypted',
        'sql_password' => 'encrypted',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected $hidden = [
        'shared_secret',
        'sql_password',
    ];

    public function statusBadge(): string
    {
        return match ($this->last_status) {
            self::STATUS_OK => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'secondary',
        };
    }
}
