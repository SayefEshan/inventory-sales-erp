<?php

namespace App\Repositories;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleRepository
{
    /**
     * Get top selling products for a given period
     */
    public function getTopSellingProducts(int $limit = 100, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $query = DB::table('sales')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.category',
                DB::raw('SUM(sales.quantity_sold) as total_quantity'),
                DB::raw('SUM(sales.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales.outlet_id) as outlet_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.category');

        if ($startDate && $endDate) {
            $query->whereBetween('sales.date', [$startDate, $endDate]);
        }

        return $query->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get monthly sales by distributor/region
     */
    public function getMonthlySalesByDistributor(int $year, int $month)
    {
        return DB::table('sales')
            ->join('outlets', 'sales.outlet_id', '=', 'outlets.id')
            ->join('distributors', 'outlets.distributor_id', '=', 'distributors.id')
            ->select(
                'distributors.id',
                'distributors.name',
                'distributors.region',
                DB::raw('SUM(sales.quantity_sold) as total_quantity'),
                DB::raw('SUM(sales.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales.outlet_id) as active_outlets'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->whereYear('sales.date', $year)
            ->whereMonth('sales.date', $month)
            ->groupBy('distributors.id', 'distributors.name', 'distributors.region')
            ->orderByDesc('total_revenue')
            ->get();
    }

    /**
     * Get sales trend for a product
     */
    public function getProductSalesTrend(int $productId, string $groupBy = 'day', int $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);

        $dateFormat = match ($groupBy) {
            'month' => '%Y-%m',
            'week' => '%Y-%u',
            default => '%Y-%m-%d'
        };

        return DB::table('sales')
            ->select(
                DB::raw("DATE_FORMAT(date, '{$dateFormat}') as period"),
                DB::raw('SUM(quantity_sold) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('AVG(unit_price) as avg_price')
            )
            ->where('product_id', $productId)
            ->where('date', '>=', $startDate)
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get paginated sales with filters
     */
    public function getSalesWithFilters(array $filters, int $perPage = 50)
    {
        $query = Sale::query()->with(['product', 'outlet.distributor']);

        if (!empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['outlet_id'])) {
            $query->where('outlet_id', $filters['outlet_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['distributor_id'])) {
            $query->whereHas('outlet', function ($q) use ($filters) {
                $q->where('distributor_id', $filters['distributor_id']);
            });
        }

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }

    /**
     * Get sales summary statistics
     */
    public function getSalesSummary(?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $query = DB::table('sales');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return $query->select(
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(quantity_sold) as total_quantity'),
            DB::raw('SUM(total_price) as total_revenue'),
            DB::raw('AVG(total_price) as avg_transaction_value')
        )->first();
    }
}
