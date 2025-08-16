<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'product_id',
        'quantity',
        'min_stock_level',
        'last_updated'
    ];

    protected $casts = [
        'outlet_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'min_stock_level' => 'integer',
        'last_updated' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Check if stock is low
    public function isLowStock()
    {
        return $this->quantity <= $this->min_stock_level;
    }

    // Scope for low stock items
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_stock_level');
    }

    // Update stock quantity
    public function updateStock($quantity, $operation = 'add')
    {
        if ($operation === 'add') {
            $this->quantity += $quantity;
        } else {
            $this->quantity -= $quantity;
        }

        $this->last_updated = now();
        $this->save();

        return $this;
    }
}
