<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Jobs\ExportSalesJob;
use App\Jobs\ImportSalesJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\SaleRepository;

class SaleController extends Controller
{
    public function __construct(
        private SaleRepository $saleRepo
    ) {}

    /**
     * Display sales list
     */
    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'outlet_id', 'product_id', 'distributor_id']);
        $sales = $this->saleRepo->getSalesWithFilters($filters);

        if ($request->wantsJson()) {
            return response()->json($sales);
        }

        return view('sales.index', compact('sales', 'filters'));
    }

    /**
     * Import sales from CSV (async)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:102400' // 100MB max
        ]);

        // Store file
        $file = $request->file('file');
        $path = $file->storeAs('imports', 'sales_' . time() . '_' . Str::random(10) . '.csv');

        // Dispatch job
        $job = new ImportSalesJob($path);
        dispatch($job);

        return response()->json([
            'message' => 'Import job queued successfully',
            'job_id' => $job->job ? $job->job->getJobId() : null,
            'status' => 'processing'
        ]);
    }

    /**
     * Export sales to CSV (async)
     */
    public function export(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'outlet_id', 'product_id', 'distributor_id']);

        // Dispatch export job
        $job = new ExportSalesJob($filters);
        $jobId = $job->getJobId();
        dispatch($job);

        return response()->json([
            'message' => 'Export job queued successfully',
            'job_id' => $jobId,
            'status' => 'processing',
            'check_url' => route('sales.export.status', ['jobId' => $jobId])
        ]);
    }

    /**
     * Check export status
     */
    public function exportStatus(string $jobId)
    {
        // Check if export is ready in cache
        $exportData = cache()->get('export_' . $jobId);

        if ($exportData) {
            return response()->json([
                'status' => 'completed',
                'download_url' => $exportData['url'],
                'created_at' => $exportData['created_at']
            ]);
        }

        // Check if job exists in queue (still processing)
        $jobExists = DB::table('jobs')
            ->where('payload', 'like', '%' . $jobId . '%')
            ->exists();

        if ($jobExists) {
            return response()->json([
                'status' => 'processing',
                'message' => 'Export is still being processed'
            ]);
        }

        // Check failed jobs
        $failedJob = DB::table('failed_jobs')
            ->where('payload', 'like', '%' . $jobId . '%')
            ->first();

        if ($failedJob) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Export job failed'
            ]);
        }

        return response()->json([
            'status' => 'not_found',
            'message' => 'Export job not found'
        ], 404);
    }

    /**
     * Download exported file
     */
    public function download(string $jobId)
    {
        $exportData = cache()->get('export_' . $jobId);

        if (!$exportData) {
            return response()->json([
                'message' => 'Export not found or expired'
            ], 404);
        }

        return response()->download(storage_path('app/' . $exportData['path']));
    }
}
