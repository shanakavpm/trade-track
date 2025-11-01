<?php

use App\Services\Kpi\KpiService;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    Redis::flushdb();
});

test('kpi service tracks revenue correctly', function () {
    $kpiService = app(KpiService::class);
    $date = now()->format('Y-m-d');

    $kpiService->incrementRevenue($date, 100.50);
    $kpiService->incrementRevenue($date, 50.25);

    $kpiData = $kpiService->getKpiForDate($date);

    expect($kpiData['revenue'])->toBe('150.75');
});

test('kpi service tracks order count correctly', function () {
    $kpiService = app(KpiService::class);
    $date = now()->format('Y-m-d');

    $kpiService->incrementOrderCount($date);
    $kpiService->incrementOrderCount($date);
    $kpiService->incrementOrderCount($date);

    $kpiData = $kpiService->getKpiForDate($date);

    expect($kpiData['order_count'])->toBe(3);
});

test('kpi service calculates average order value', function () {
    $kpiService = app(KpiService::class);
    $date = now()->format('Y-m-d');

    $kpiService->incrementSumTotal($date, 100.00);
    $kpiService->incrementSumTotal($date, 200.00);
    $kpiService->incrementOrderCount($date);
    $kpiService->incrementOrderCount($date);

    $kpiData = $kpiService->getKpiForDate($date);

    expect($kpiData['avg_order_value'])->toBe('150.00');
});

test('leaderboard tracks customer spending', function () {
    $kpiService = app(KpiService::class);
    $month = now()->format('Y-m');

    $kpiService->addToLeaderboard($month, 1, 100.00);
    $kpiService->addToLeaderboard($month, 2, 200.00);
    $kpiService->addToLeaderboard($month, 1, 50.00);

    $leaderboard = $kpiService->getLeaderboard($month, 10);

    expect($leaderboard)->toHaveCount(2)
        ->and($leaderboard[0]['customer_id'])->toBe(2)
        ->and($leaderboard[0]['total'])->toBe('200.00')
        ->and($leaderboard[1]['customer_id'])->toBe(1)
        ->and($leaderboard[1]['total'])->toBe('150.00');
});

test('refund updates kpis correctly', function () {
    $kpiService = app(KpiService::class);
    $date = now()->format('Y-m-d');

    $kpiService->incrementRevenue($date, 100.00);
    $kpiService->incrementOrderCount($date);
    $kpiService->incrementSumTotal($date, 100.00);

    $kpiService->decrementRevenue($date, 30.00);
    $kpiService->decrementSumTotal($date, 30.00);

    $kpiData = $kpiService->getKpiForDate($date);

    expect($kpiData['revenue'])->toBe('70.00')
        ->and($kpiData['order_count'])->toBe(1)
        ->and($kpiData['avg_order_value'])->toBe('70.00');
});
