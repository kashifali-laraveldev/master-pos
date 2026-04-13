<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'sku',
        'description',
        'image',
        'images',
        'unit_type',
        'unit_label',
        'price_per_unit',
        'cost_price',
        'stock_quantity',
        'low_stock_alert',
        'stock_unit',
        'is_active',
        'is_featured',
        'display_order',
    ];

    protected $casts = [
        'images' => 'array',
        'price_per_unit' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'decimal:3',
        'low_stock_alert' => 'decimal:3',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_alert;
    }

    public function deductStock(float $qty, ?int $saleId = null, ?int $userId = null, string $type = 'sale', string $notes = ''): void
    {
        $before = (float) $this->stock_quantity;

        $this->decrement('stock_quantity', $qty);

        $this->stockMovements()->create([
            'type' => $type,
            'quantity' => -$qty,
            'quantity_before' => $before,
            'quantity_after' => $before - $qty,
            'notes' => $notes,
            'user_id' => $userId,
            'sale_id' => $saleId,
        ]);
    }

    public function addStock(float $qty, string $type = 'purchase', string $notes = '', ?int $userId = null, ?int $saleId = null): void
    {
        $before = (float) $this->stock_quantity;

        $this->increment('stock_quantity', $qty);

        $this->stockMovements()->create([
            'type' => $type,
            'quantity' => $qty,
            'quantity_before' => $before,
            'quantity_after' => $before + $qty,
            'notes' => $notes,
            'user_id' => $userId,
            'sale_id' => $saleId,
        ]);
    }
}

