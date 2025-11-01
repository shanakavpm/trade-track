<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Kpi\KpiService;
use Illuminate\Http\JsonResponse;

class KpiController extends Controller
{
    public function __construct(
        private KpiService $kpiService,
    ) {}

    public function today(): JsonResponse
    {
        $date = now()->format('Y-m-d');
        $kpiData = $this->kpiService->getKpiForDate($date);

        return response()->json([
            'date' => $date,
            'revenue' => $kpiData['revenue'],
            'order_count' => $kpiData['order_count'],
            'avg_order_value' => $kpiData['avg_order_value'],
        ]);
    }

    public function leaderboard(): JsonResponse
    {
        $month = now()->format('Y-m');
        $leaderboard = $this->kpiService->getLeaderboard($month, 10);

        return response()->json([
            'month' => $month,
            'leaderboard' => $leaderboard,
        ]);
    }
}
