<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    /**
     * Get products with low stock across outlets
     */
    public function getLowStockProducts(int $limit = 20, bool $paginate = false)
    {
        $query = DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('outlets', 'inventories.outlet_id', '=', 'outlets.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.category',
                'outlets.name as outlet_name',
                'outlets.city',
                'inventories.quantity',
                'inventories.min_stock_level',
                DB::raw('(inventories.min_stock_level - inventories.quantity) as shortage')
            )
            ->whereColumn('inventories.quantity', '<=', 'inventories.min_stock_level')
            ->orderByDesc('shortage');

        if ($paginate) {
            return $query->paginate($limit);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get total count of products with low stock
     */
    public function getLowStockCount(): int
    {
        return DB::table('inventories')
            ->whereColumn('inventories.quantity', '<=', 'inventories.min_stock_level')
            ->count();
    }

    /**
     * Get product performance metrics
     */
    public function getProductPerformance(int $productId, int $days = 30)
    {
        $startDate = now()->subDays($days);

        return DB::table('sales')
            ->where('product_id', $productId)
            ->where('date', '>=', $startDate)
            ->select(
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(quantity_sold) as total_sold'),
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('AVG(unit_price) as avg_selling_price'),
                DB::raw('MIN(unit_price) as min_price'),
                DB::raw('MAX(unit_price) as max_price'),
                DB::raw('COUNT(DISTINCT outlet_id) as outlets_selling')
            )
            ->first();
    }

    /**
     * Get products by category with stock info
     */
    public function getProductsByCategory(string $category)
    {
        return Product::where('category', $category)
            ->withCount('inventories')
            ->withSum('inventories', 'quantity')
            ->orderBy('name')
            ->get();
    }

    /**
     * Search products
     */
    public function searchProducts(string $term, int $limit = 20)
    {
        return Product::where('name', 'like', "%{$term}%")
            ->orWhere('sku', 'like', "%{$term}%")
            ->limit($limit)
            ->get();
    }
}
