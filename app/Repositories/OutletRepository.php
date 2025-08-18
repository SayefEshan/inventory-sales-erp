<?php

namespace App\Repositories;

use App\Models\Outlet;
use Illuminate\Support\Facades\DB;

class OutletRepository
{
    /**
     * Get outlets with low inventory count
     */
    public function getOutletsWithLowInventory(int $threshold = 10)
    {
        return DB::table('outlets')
            ->join('inventories', 'outlets.id', '=', 'inventories.outlet_id')
            ->select(
                'outlets.id',
                'outlets.name',
                'outlets.city',
                'outlets.state',
                DB::raw('COUNT(CASE WHEN inventories.quantity <= inventories.min_stock_level THEN 1 END) as low_stock_count'),
                DB::raw('COUNT(inventories.id) as total_products')
            )
            ->groupBy('outlets.id', 'outlets.name', 'outlets.city', 'outlets.state')
            ->having('low_stock_count', '>=', $threshold)
            ->orderByDesc('low_stock_count')
            ->get();
    }

    /**
     * Get outlet performance metrics
     */
    public function getOutletPerformance(int $outletId, int $days = 30)
    {
        $startDate = now()->subDays($days);

        return DB::table('sales')
            ->where('outlet_id', $outletId)
            ->where('date', '>=', $startDate)
            ->select(
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(quantity_sold) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT product_id) as unique_products'),
                DB::raw('AVG(total_price) as avg_transaction_value')
            )
            ->first();
    }

    /**
     * Get outlets by distributor
     */
    public function getOutletsByDistributor(int $distributorId)
    {
        return Outlet::where('distributor_id', $distributorId)
            ->withCount('sales')
            ->orderBy('name')
            ->get();
    }
}
