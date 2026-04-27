<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    public const METHOD_PAKASIR = 'pakasir';

    public const METHOD_TRANSFER = 'transfer';

    public const METHOD_CASH = 'cash';

    public const METHOD_OTHER = 'other';

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'amount_idr',
        'method',
        'paid_at',
        'reference',
        'proof_path',
        'status',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
