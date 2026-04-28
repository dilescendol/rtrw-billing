<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use BelongsToTenant, HasFactory;

    public const EVENT_INVOICE_CREATED = 'invoice_created';

    public const EVENT_INVOICE_DUE_SOON = 'invoice_due_soon';

    public const EVENT_INVOICE_OVERDUE = 'invoice_overdue';

    public const EVENT_PAYMENT_RECEIVED = 'payment_received';

    public const EVENT_CUSTOMER_ISOLATED = 'customer_isolated';

    public const EVENT_VOUCHER_CREATED = 'voucher_created';

    public const EVENTS = [
        self::EVENT_INVOICE_CREATED,
        self::EVENT_INVOICE_DUE_SOON,
        self::EVENT_INVOICE_OVERDUE,
        self::EVENT_PAYMENT_RECEIVED,
        self::EVENT_CUSTOMER_ISOLATED,
        self::EVENT_VOUCHER_CREATED,
    ];

    public const DEFAULTS = [
        self::EVENT_INVOICE_CREATED => "Halo {{nama}},\nInvoice {{nomor_invoice}} sebesar Rp {{jumlah}} sudah terbit.\nJatuh tempo: {{jatuh_tempo}}.\nLink bayar: {{link_bayar}}\nTerima kasih.",
        self::EVENT_INVOICE_DUE_SOON => "Halo {{nama}},\nPengingat tagihan {{nomor_invoice}} sebesar Rp {{jumlah}} jatuh tempo {{jatuh_tempo}}.\nMohon segera diselesaikan. Link: {{link_bayar}}",
        self::EVENT_INVOICE_OVERDUE => "Halo {{nama}},\nTagihan {{nomor_invoice}} (Rp {{jumlah}}) sudah melewati jatuh tempo {{jatuh_tempo}}.\nMohon segera dibayar untuk menghindari pemutusan layanan.\nLink: {{link_bayar}}",
        self::EVENT_PAYMENT_RECEIVED => "Halo {{nama}},\nTerima kasih, pembayaran invoice {{nomor_invoice}} sebesar Rp {{jumlah}} telah kami terima.",
        self::EVENT_CUSTOMER_ISOLATED => "Halo {{nama}},\nLayanan internet Anda telah diisolir karena tunggakan invoice {{nomor_invoice}}.\nLakukan pelunasan untuk pengaktifan kembali.",
        self::EVENT_VOUCHER_CREATED => "Halo {{nama}},\nVoucher hotspot Anda: {{kode}}\nProfile: {{profile}}\nMasa berlaku: {{durasi}}.",
    ];

    protected $fillable = [
        'tenant_id',
        'event',
        'name',
        'message',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function eventLabel(string $event): string
    {
        return match ($event) {
            self::EVENT_INVOICE_CREATED => 'Invoice baru',
            self::EVENT_INVOICE_DUE_SOON => 'Pengingat jatuh tempo',
            self::EVENT_INVOICE_OVERDUE => 'Invoice menunggak',
            self::EVENT_PAYMENT_RECEIVED => 'Pembayaran diterima',
            self::EVENT_CUSTOMER_ISOLATED => 'Pelanggan diisolir',
            self::EVENT_VOUCHER_CREATED => 'Voucher dibuat',
            default => ucfirst(str_replace('_', ' ', $event)),
        };
    }
}
