<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'distributor_id',
        'city',
        'state',
        'pincode',
        'contact_person',
        'phone'
    ];

    protected $casts = [
        'distributor_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Scope for city filtering
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    // Scope for state filtering
    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    // Get full address
    public function getFullAddressAttribute()
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->pincode
        ]));
    }
}
