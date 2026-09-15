<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ReportRequest;
use App\Services\ReportService;

class ReportController extends Controller
{
    /**
     * @var ReportService
     */
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Show orders between two dates.
     *
     * @return \Illuminate\View\View
     */
    public function index(ReportRequest $request)
    {
        $report = $this->reportService->forDateRange(
            $request->input('from'),
            $request->input('to')
        );

        return view('reports.index', $report);
    }
}
