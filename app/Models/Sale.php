<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'product_id',
        'date',
        'quantity_sold',
        'unit_price',
        'total_price'
    ];

    protected $casts = [
        'outlet_id' => 'integer',
        'product_id' => 'integer',
        'date' => 'date',
        'quantity_sold' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
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

    // Scope for date range
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    // Scope for current month
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
    }

    // Scope for last month
    public function scopeLastMonth($query)
    {
        $lastMonth = now()->subMonth();
        return $query->whereMonth('date', $lastMonth->month)
            ->whereYear('date', $lastMonth->year);
    }

    // Calculate total before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($sale) {
            if ($sale->quantity_sold && $sale->unit_price) {
                $sale->total_price = $sale->quantity_sold * $sale->unit_price;
            }
        });
    }
}
