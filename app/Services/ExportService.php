<?php

namespace App\Services;

use App\Repositories\SaleRepository;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;

class ExportService
{
    public function __construct(
        private SaleRepository $saleRepo
    ) {}

    /**
     * Export sales data to CSV
     */
    public function exportSalesToCsv(array $filters = []): string
    {
        $filename = 'sales_export_' . now()->format('Y_m_d_His') . '.csv';
        $path = storage_path('app/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        // Create CSV writer
        $csv = Writer::createFromPath($path, 'w+');

        // Add headers
        $csv->insertOne([
            'Sale ID',
            'Date',
            'Outlet Name',
            'Product Name',
            'SKU',
            'Quantity',
            'Unit Price',
            'Total Price',
            'Distributor',
            'Region'
        ]);

        // Query data in chunks
        $this->queryAndWriteSales($csv, $filters);

        return $path;
    }

    /**
     * Query and write sales data in chunks
     */
    private function queryAndWriteSales($csv, array $filters): void
    {
        $query = DB::table('sales')
            ->join('outlets', 'sales.outlet_id', '=', 'outlets.id')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->join('distributors', 'outlets.distributor_id', '=', 'distributors.id')
            ->select([
                'sales.id',
                'sales.date',
                'outlets.name as outlet_name',
                'products.name as product_name',
                'products.sku',
                'sales.quantity_sold',
                'sales.unit_price',
                'sales.total_price',
                'distributors.name as distributor_name',
                'distributors.region'
            ]);

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->where('sales.date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('sales.date', '<=', $filters['date_to']);
        }

        if (!empty($filters['outlet_id'])) {
            $query->where('sales.outlet_id', $filters['outlet_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('sales.product_id', $filters['product_id']);
        }

        if (!empty($filters['distributor_id'])) {
            $query->where('outlets.distributor_id', $filters['distributor_id']);
        }

        // Process in chunks of 5000
        $query->orderBy('sales.date', 'desc')
            ->chunk(5000, function ($sales) use ($csv) {
                $records = [];

                foreach ($sales as $sale) {
                    $records[] = [
                        $sale->id,
                        $sale->date,
                        $sale->outlet_name,
                        $sale->product_name,
                        $sale->sku,
                        $sale->quantity_sold,
                        $sale->unit_price,
                        $sale->total_price,
                        $sale->distributor_name,
                        $sale->region
                    ];
                }

                $csv->insertAll($records);
            });
    }

    /**
     * Export report to PDF (simplified for now)
     */
    public function exportReportToPdf(string $reportType, array $data): string
    {
        // This would use a PDF library like DomPDF or TCPDF
        // For now, returning a placeholder

        $filename = "{$reportType}_" . now()->format('Y_m_d_His') . '.pdf';
        $path = storage_path('app/exports/' . $filename);

        // In real implementation, generate PDF here
        file_put_contents($path, 'PDF content would be here');

        return $path;
    }
}
