<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\ExportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ExportService $exportService
    ) {}

    /**
     * Get top selling products
     */
    public function topProducts(Request $request)
    {
        $period = $request->get('period', 'last_month');
        $report = $this->reportService->getTopSellingProductsReport($period);

        if ($request->wantsJson()) {
            return response()->json($report);
        }

        return view('reports.top-products', compact('report'));
    }

    /**
     * Get monthly sales report
     */
    public function monthlySales(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $report = $this->reportService->getMonthlySalesReport($year, $month);

        if ($request->wantsJson()) {
            return response()->json($report);
        }

        return view('reports.monthly-sales', compact('report'));
    }

    /**
     * Get low stock alerts
     */
    public function lowStock(Request $request)
    {
        $report = $this->reportService->getLowStockReport();

        if ($request->wantsJson()) {
            return response()->json($report);
        }

        return view('reports.low-stock', compact('report'));
    }

    /**
     * Get sales trend for a product
     */
    public function salesTrend(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'group_by' => 'in:day,week,month',
            'days' => 'integer|min:7|max:365'
        ]);

        $report = $this->reportService->getProductSalesTrend(
            $request->product_id,
            $request->get('group_by', 'day'),
            $request->get('days', 30)
        );

        if ($request->wantsJson()) {
            return response()->json($report);
        }

        return view('reports.sales-trend', compact('report'));
    }
}
