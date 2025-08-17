<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Show dashboard
     */
    public function index()
    {
        $summary = $this->reportService->getDashboardSummary();

        return view('dashboard', compact('summary'));
    }
}
