<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sale_id',
        'product_id',
        'product_name',
        'unit_type',
        'unit_label',
        'quantity',
        'price_per_unit',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price_per_unit' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}

