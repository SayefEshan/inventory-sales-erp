<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Outlet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\LazyCollection;

class ImportService
{
    private array $errors = [];
    private int $successCount = 0;
    private int $failedCount = 0;

    /**
     * Import sales from CSV file
     */
    public function importSalesFromCsv(string $filePath): array
    {
        $this->resetCounters();

        // Cache lookups for performance
        $products = Product::pluck('id', 'sku')->toArray();
        $outlets = Outlet::pluck('id')->toArray();

        // Use lazy collection for memory efficiency
        LazyCollection::make(function () use ($filePath) {
            $handle = fopen($filePath, 'r');

            // Skip header
            $header = fgetcsv($handle);

            while (($line = fgetcsv($handle)) !== false) {
                yield $line;
            }

            fclose($handle);
        })
            ->chunk(1000)
            ->each(function ($chunk) use ($products, $outlets) {
                $this->processSalesChunk($chunk, $products, $outlets);
            });

        return [
            'success' => $this->successCount,
            'failed' => $this->failedCount,
            'errors' => array_slice($this->errors, 0, 100) // Limit errors to first 100
        ];
    }

    /**
     * Process a chunk of sales data
     */
    private function processSalesChunk($chunk, array $products, array $outlets): void
    {
        $sales = [];

        foreach ($chunk as $index => $row) {
            // Validate row
            $validation = $this->validateSalesRow($row, $index);

            if (!$validation['valid']) {
                $this->failedCount++;
                $this->errors[] = $validation['error'];
                continue;
            }

            // Map CSV columns
            [$outletId, $productId, $date, $quantitySold, $totalPrice] = $row;

            // Validate IDs exist
            if (!in_array($outletId, $outlets)) {
                $this->failedCount++;
                $this->errors[] = "Row {$index}: Outlet ID {$outletId} not found";
                continue;
            }

            if (!isset($products[$productId]) && !in_array($productId, $products)) {
                $this->failedCount++;
                $this->errors[] = "Row {$index}: Product {$productId} not found";
                continue;
            }

            // Calculate unit price
            $unitPrice = $quantitySold > 0 ? $totalPrice / $quantitySold : 0;

            $sales[] = [
                'outlet_id' => $outletId,
                'product_id' => is_numeric($productId) ? $productId : ($products[$productId] ?? null),
                'date' => $date,
                'quantity_sold' => $quantitySold,
                'unit_price' => round($unitPrice, 2),
                'total_price' => $totalPrice,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $this->successCount++;
        }

        // Bulk insert
        if (!empty($sales)) {
            DB::table('sales')->insert($sales);
        }
    }

    /**
     * Validate a sales row
     */
    private function validateSalesRow(array $row, int $index): array
    {
        if (count($row) < 5) {
            return [
                'valid' => false,
                'error' => "Row {$index}: Invalid column count"
            ];
        }

        $rules = [
            'outlet_id' => 'required|numeric',
            'product_id' => 'required',
            'date' => 'required|date',
            'quantity_sold' => 'required|numeric|min:1',
            'total_price' => 'required|numeric|min:0'
        ];

        $data = [
            'outlet_id' => $row[0] ?? null,
            'product_id' => $row[1] ?? null,
            'date' => $row[2] ?? null,
            'quantity_sold' => $row[3] ?? null,
            'total_price' => $row[4] ?? null
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'error' => "Row {$index}: " . $validator->errors()->first()
            ];
        }

        return ['valid' => true];
    }

    /**
     * Reset counters
     */
    private function resetCounters(): void
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->failedCount = 0;
    }
}
