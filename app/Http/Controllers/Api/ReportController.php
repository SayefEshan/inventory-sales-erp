<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Get top selling products
     * 
     * @authenticated
     * @queryParam period string Period for report (today, this_week, this_month, last_month). Example: last_month
     * @queryParam limit integer Number of products to return (max 100). Example: 20
     */
    public function topProducts(Request $request)
    {
        $validated = $request->validate([
            'period' => 'string|in:today,this_week,this_month,last_month',
            'limit' => 'integer|min:1|max:100'
        ]);

        $period = $validated['period'] ?? 'last_month';
        $limit = $validated['limit'] ?? 20;

        try {
            $report = $this->reportService->getTopSellingProductsReport($period);

            // Limit results if specified
            if (isset($report['products']) && $limit < count($report['products'])) {
                $report['products'] = array_slice($report['products'], 0, $limit);
            }

            return response()->json([
                'success' => true,
                'data' => $report,
                'meta' => [
                    'total_products' => count($report['products']),
                    'period' => $period,
                    'limit' => $limit,
                    'generated_at' => now()->toIso8601String()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Top products report failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => 'Report service unavailable'
            ], 500);
        }
    }

    /**
     * Get sales summary
     * 
     * @authenticated
     * @queryParam date_from date Start date. Example: 2024-01-01
     * @queryParam date_to date End date. Example: 2024-12-31
     */
    public function salesSummary(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        try {
            $summary = $this->reportService->getDashboardSummary();

            // Add custom date range if provided
            if ($request->date_from && $request->date_to) {
                $customPeriod = $this->reportService->getSalesSummary(
                    \Carbon\Carbon::parse($request->date_from),
                    \Carbon\Carbon::parse($request->date_to)
                );
                $summary['custom_period'] = $customPeriod;
            }

            return response()->json([
                'success' => true,
                'data' => $summary,
                'meta' => [
                    'currency' => 'BDT',
                    'timezone' => config('app.timezone'),
                    'generated_at' => now()->toIso8601String()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Sales summary report failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales summary',
                'error' => 'Report service unavailable'
            ], 500);
        }
    }
}
