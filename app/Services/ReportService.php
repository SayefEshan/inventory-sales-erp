<?php

namespace App\Services;

use App\Repositories\SaleRepository;
use App\Repositories\ProductRepository;
use App\Repositories\OutletRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    public function __construct(
        private SaleRepository $saleRepo,
        private ProductRepository $productRepo,
        private OutletRepository $outletRepo
    ) {}

    /**
     * Generate top selling products report
     */
    public function getTopSellingProductsReport(?string $period = 'last_month', bool $paginate = false)
    {
        $dates = $this->getPeriodDates($period);

        if ($paginate) {
            // Don't cache paginated results
            $products = $this->saleRepo->getTopSellingProducts(
                20, // Per page limit for pagination
                $dates['start'],
                $dates['end'],
                true
            );

            return [
                'period' => $period,
                'start_date' => $dates['start']->format('Y-m-d'),
                'end_date' => $dates['end']->format('Y-m-d'),
                'products' => $products,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];
        }

        $cacheKey = "top_products_{$period}";

        return Cache::remember($cacheKey, 1800, function () use ($period) {
            $dates = $this->getPeriodDates($period);

            $products = $this->saleRepo->getTopSellingProducts(
                20,
                $dates['start'],
                $dates['end']
            );

            return [
                'period' => $period,
                'start_date' => $dates['start']->format('Y-m-d'),
                'end_date' => $dates['end']->format('Y-m-d'),
                'products' => $products,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];
        });
    }

    /**
     * Generate monthly sales report
     */
    public function getMonthlySalesReport(int $year, int $month, bool $paginate = false)
    {
        if ($paginate) {
            // Don't cache paginated results
            $distributorSales = $this->saleRepo->getMonthlySalesByDistributor($year, $month, true, 20);

            // Get totals separately for paginated results
            $allDistributorSales = $this->saleRepo->getMonthlySalesByDistributor($year, $month, false);
            $totals = [
                'total_revenue' => $allDistributorSales->sum('total_revenue'),
                'total_quantity' => $allDistributorSales->sum('total_quantity'),
                'total_transactions' => $allDistributorSales->sum('transaction_count'),
                'active_distributors' => $allDistributorSales->count()
            ];

            return [
                'year' => $year,
                'month' => $month,
                'distributors' => $distributorSales,
                'totals' => $totals,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];
        }

        $cacheKey = "monthly_sales_{$year}_{$month}";

        return Cache::remember($cacheKey, 3600, function () use ($year, $month) {
            $distributorSales = $this->saleRepo->getMonthlySalesByDistributor($year, $month);

            $totals = [
                'total_revenue' => $distributorSales->sum('total_revenue'),
                'total_quantity' => $distributorSales->sum('total_quantity'),
                'total_transactions' => $distributorSales->sum('transaction_count'),
                'active_distributors' => $distributorSales->count()
            ];

            return [
                'year' => $year,
                'month' => $month,
                'distributors' => $distributorSales,
                'totals' => $totals,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];
        });
    }

    /**
     * Generate low stock alerts report
     */
    public function getLowStockReport(bool $paginate = false)
    {
        // Don't cache this - needs to be real-time
        if ($paginate) {
            $lowStockProducts = $this->productRepo->getLowStockProducts(20, true);
            $outletsWithIssues = $this->outletRepo->getOutletsWithLowInventory(5, true, 20);
        } else {
            $lowStockProducts = $this->productRepo->getLowStockProducts(20);
            $outletsWithIssues = $this->outletRepo->getOutletsWithLowInventory(5);
        }

        return [
            'products' => $lowStockProducts,
            'outlets' => $outletsWithIssues,
            'total_alerts' => $paginate ? $lowStockProducts->total() : $lowStockProducts->count(),
            'affected_outlets' => $paginate ? $outletsWithIssues->total() : $outletsWithIssues->count(),
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate sales trend report for a product
     */
    public function getProductSalesTrend(int $productId, string $groupBy = 'day', int $days = 30)
    {
        $cacheKey = "product_trend_{$productId}_{$groupBy}_{$days}";

        return Cache::remember($cacheKey, 1800, function () use ($productId, $groupBy, $days) {
            $trend = $this->saleRepo->getProductSalesTrend($productId, $groupBy, $days);
            $performance = $this->productRepo->getProductPerformance($productId, $days);

            return [
                'product_id' => $productId,
                'period_days' => $days,
                'group_by' => $groupBy,
                'trend_data' => $trend,
                'summary' => $performance,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];
        });
    }

    /**
     * Get dashboard summary
     */
    public function getDashboardSummary()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => $this->saleRepo->getSalesSummary($today, $today),
            'this_month' => $this->saleRepo->getSalesSummary($thisMonth, $today),
            'top_products_today' => $this->saleRepo->getTopSellingProducts(5, $today, $today),
            'low_stock_count' => $this->productRepo->getLowStockProducts(5)->count()
        ];
    }

    /**
     * Get sales summary for custom date range
     */
    public function getSalesSummary(Carbon $startDate, Carbon $endDate)
    {
        return $this->saleRepo->getSalesSummary($startDate, $endDate);
    }

    /**
     * Helper to get period dates
     */
    private function getPeriodDates(string $period): array
    {
        return match ($period) {
            'today' => [
                'start' => Carbon::today(),
                'end' => Carbon::today()
            ],
            'this_week' => [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek()
            ],
            'this_month' => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth()
            ],
            'last_month' => [
                'start' => Carbon::now()->subMonth()->startOfMonth(),
                'end' => Carbon::now()->subMonth()->endOfMonth()
            ],
            default => [
                'start' => Carbon::now()->subMonth(),
                'end' => Carbon::now()
            ]
        };
    }
}
