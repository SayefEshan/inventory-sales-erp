<?php

namespace App\Jobs;

use App\Services\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportSalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 3600; // 1 hour for large exports

    /**
     * The export file name
     */
    private string $fileName;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $filters = [],
        public ?string $userEmail = null
    ) {
        $this->fileName = 'sales_export_' . Str::random(10) . '_' . now()->format('Y_m_d_His') . '.csv';
    }

    /**
     * Execute the job.
     */
    public function handle(ExportService $exportService): void
    {
        Log::info('Starting sales export job', [
            'filters' => $this->filters,
            'fileName' => $this->fileName
        ]);

        try {
            // Process the export
            $filePath = $exportService->exportSalesToCsv($this->filters);

            // Move to public storage for download
            $publicPath = 'exports/' . $this->fileName;
            Storage::disk('public')->put($publicPath, file_get_contents($filePath));

            // Delete the temp file
            unlink($filePath);

            // Store download URL in cache for 24 hours
            $downloadUrl = Storage::disk('public')->url($publicPath);
            cache()->put(
                'export_' . $this->fileName,
                [
                    'url' => $downloadUrl,
                    'path' => $publicPath,
                    'created_at' => now()->toDateTimeString()
                ],
                86400 // 24 hours
            );

            Log::info('Sales export completed', [
                'fileName' => $this->fileName,
                'url' => $downloadUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Sales export failed', [
                'fileName' => $this->fileName,
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
        Log::error('Sales export job failed permanently', [
            'fileName' => $this->fileName,
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Get the job identifier.
     */
    public function getJobId(): string
    {
        return $this->fileName;
    }
}
