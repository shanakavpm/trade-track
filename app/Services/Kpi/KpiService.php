<?php

namespace App\Services\Kpi;

use Illuminate\Support\Facades\Redis;

class KpiService
{
    private const PREFIX = 'kpi:';
    private const LEADERBOARD_PREFIX = 'leaderboard:';
    private const TTL = 86400 * 90; // 90 days

    public function incrementRevenue(string $date, float $amount): void
    {
        $key = self::PREFIX . $date . ':revenue';
        Redis::incrbyfloat($key, number_format($amount, 2, '.', ''));
        Redis::expire($key, self::TTL);
    }

    public function decrementRevenue(string $date, float $amount): void
    {
        $key = self::PREFIX . $date . ':revenue';
        Redis::incrbyfloat($key, -1 * number_format($amount, 2, '.', ''));
        Redis::expire($key, self::TTL);
    }

    public function incrementOrderCount(string $date): void
    {
        $key = self::PREFIX . $date . ':order_count';
        Redis::incr($key);
        Redis::expire($key, self::TTL);
    }

    public function decrementOrderCount(string $date): void
    {
        $key = self::PREFIX . $date . ':order_count';
        Redis::decr($key);
        Redis::expire($key, self::TTL);
    }

    public function incrementSumTotal(string $date, float $amount): void
    {
        $key = self::PREFIX . $date . ':sum_total';
        Redis::incrbyfloat($key, number_format($amount, 2, '.', ''));
        Redis::expire($key, self::TTL);
    }

    public function decrementSumTotal(string $date, float $amount): void
    {
        $key = self::PREFIX . $date . ':sum_total';
        Redis::incrbyfloat($key, -1 * number_format($amount, 2, '.', ''));
        Redis::expire($key, self::TTL);
    }

    public function getKpiForDate(string $date): array
    {
        $revenue = (float) Redis::get(self::PREFIX . $date . ':revenue') ?: 0;
        $orderCount = (int) Redis::get(self::PREFIX . $date . ':order_count') ?: 0;
        $sumTotal = (float) Redis::get(self::PREFIX . $date . ':sum_total') ?: 0;

        $avgOrderValue = $orderCount > 0 ? $sumTotal / $orderCount : 0;

        return [
            'revenue' => number_format($revenue, 2, '.', ''),
            'order_count' => $orderCount,
            'avg_order_value' => number_format($avgOrderValue, 2, '.', ''),
        ];
    }

    public function addToLeaderboard(string $month, int $customerId, float $amount): void
    {
        $key = self::LEADERBOARD_PREFIX . $month;
        Redis::zincrby($key, number_format($amount, 2, '.', ''), $customerId);
        Redis::expire($key, self::TTL);
    }

    public function removeFromLeaderboard(string $month, int $customerId, float $amount): void
    {
        $key = self::LEADERBOARD_PREFIX . $month;
        Redis::zincrby($key, -1 * number_format($amount, 2, '.', ''), $customerId);
        Redis::expire($key, self::TTL);
    }

    public function getLeaderboard(string $month, int $limit = 10): array
    {
        $key = self::LEADERBOARD_PREFIX . $month;
        $results = Redis::zrevrange($key, 0, $limit - 1, 'WITHSCORES');

        $leaderboard = [];
        foreach ($results as $customerId => $total) {
            $leaderboard[] = [
                'customer_id' => (int) $customerId,
                'total' => number_format((float) $total, 2, '.', ''),
            ];
        }

        return $leaderboard;
    }

    public function getAllKpiKeys(): array
    {
        return Redis::keys(self::PREFIX . '*');
    }

    public function deleteKey(string $key): void
    {
        Redis::del($key);
    }
}
