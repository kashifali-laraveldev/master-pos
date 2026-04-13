<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'invoice_number',
        'user_id',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'change_amount',
        'payment_method',
        'status',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'MD-' . date('Ymd') . '-';
        // Use invoice_number ordering (not created_at) to avoid ties when seeding inserts quickly.
        $last = self::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->first();
        $seq = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}

