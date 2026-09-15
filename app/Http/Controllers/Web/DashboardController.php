<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    /**
     * @var DashboardService
     */
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Show inventory summary statistics.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('dashboard.index', [
            'stats' => $this->dashboardService->stats(),
            'lowStockProducts' => $this->dashboardService->lowStockProducts(8),
        ]);
    }
}
