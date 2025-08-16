<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'unit',
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Scope for category filtering
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Get formatted price
    public function getFormattedPriceAttribute()
    {
        return '₹' . number_format($this->price, 2);
    }
}
