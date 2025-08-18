<?php

namespace App\Jobs;

use App\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportSalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 3600; // 1 hour for large files

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $filePath,
        public ?string $userEmail = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ImportService $importService): void
    {
        Log::info('Starting sales import job', ['file' => $this->filePath]);

        try {
            // Get full path
            $fullPath = Storage::path($this->filePath);

            if (!file_exists($fullPath)) {
                throw new \Exception("File not found: {$this->filePath}");
            }

            // Process the import
            $result = $importService->importSalesFromCsv($fullPath);

            Log::info('Sales import completed', [
                'file' => $this->filePath,
                'success' => $result['success'],
                'failed' => $result['failed']
            ]);

            // Clean up the uploaded file
            Storage::delete($this->filePath);
        } catch (\Exception $e) {
            Log::error('Sales import failed', [
                'file' => $this->filePath,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Sales import job failed permanently', [
            'file' => $this->filePath,
            'error' => $exception->getMessage()
        ]);

        // Clean up the file even if job failed
        Storage::delete($this->filePath);
    }
}
