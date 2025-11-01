<?php

namespace App\Console\Commands;

use App\Models\KpiDaily;
use App\Services\Kpi\KpiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class KpiSnapshotCommand extends Command
{
    protected $signature = 'kpi:snapshot {--date= : Date to snapshot (Y-m-d format)}';
    protected $description = 'Snapshot KPI data from Redis to database';

    public function __construct(
        private KpiService $kpiService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->format('Y-m-d');

        try {
            $this->info("Creating KPI snapshot for {$date}...");

            $kpiData = $this->kpiService->getKpiForDate($date);

            DB::transaction(function () use ($date, $kpiData) {
                KpiDaily::updateOrCreate(
                    ['date' => $date],
                    [
                        'revenue' => $kpiData['revenue'],
                        'order_count' => $kpiData['order_count'],
                        'sum_total' => $kpiData['revenue'], // Same as revenue for completed orders
                        'avg_order_value' => $kpiData['avg_order_value'],
                    ]
                );
            });

            $this->info('KPI snapshot created successfully');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Revenue', '$' . $kpiData['revenue']],
                    ['Order Count', $kpiData['order_count']],
                    ['Avg Order Value', '$' . $kpiData['avg_order_value']],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Snapshot failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
