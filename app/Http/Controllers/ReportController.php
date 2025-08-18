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
        $paginate = true; // Always paginate for web views
        $report = $this->reportService->getTopSellingProductsReport($period, $paginate);

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
        $paginate = true; // Always paginate for web views

        $report = $this->reportService->getMonthlySalesReport($year, $month, $paginate);

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
        $paginate = true; // Always paginate for web views
        $report = $this->reportService->getLowStockReport($paginate);

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

    /**
     * Export top products report
     */
    public function exportTopProducts(Request $request)
    {
        $period = $request->get('period', 'last_month');
        $report = $this->reportService->getTopSellingProductsReport($period, false);
        
        $filePath = $this->exportService->exportReportToCsv('top_products', $report);
        
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Export monthly sales report
     */
    public function exportMonthlySales(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $report = $this->reportService->getMonthlySalesReport($year, $month, false);
        
        $filePath = $this->exportService->exportReportToCsv('monthly_sales', $report);
        
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Export low stock report
     */
    public function exportLowStock(Request $request)
    {
        $report = $this->reportService->getLowStockReport(false);
        
        $filePath = $this->exportService->exportReportToCsv('low_stock', $report);
        
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Export sales trend report
     */
    public function exportSalesTrend(Request $request)
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
        
        $filePath = $this->exportService->exportReportToCsv('sales_trend', $report);
        
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
