<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region',
        'contact_person',
        'email',
        'phone',
        'address'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function outlets()
    {
        return $this->hasMany(Outlet::class);
    }

    // Get sales through outlets
    public function sales()
    {
        return $this->hasManyThrough(Sale::class, Outlet::class);
    }

    // Get total outlets count
    public function getOutletCountAttribute()
    {
        return $this->outlets()->count();
    }
}
