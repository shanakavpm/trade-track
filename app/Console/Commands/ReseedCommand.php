<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ReseedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reseed {--fresh : Run migrations fresh before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reseed the database with fresh data (optionally with --fresh flag)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('═══════════════════════════════════════');
        $this->info('  Database Reseed Command');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        if ($this->option('fresh')) {
            $this->warn('⚠️  Running migrate:fresh will DROP ALL TABLES!');
            
            if (!$this->confirm('Do you want to continue?', false)) {
                $this->info('Operation cancelled.');
                return self::FAILURE;
            }

            $this->info('Running migrations fresh...');
            Artisan::call('migrate:fresh', [], $this->getOutput());
            $this->newLine();
        }

        $this->info('Seeding database...');
        Artisan::call('db:seed', [], $this->getOutput());
        $this->newLine();

        // Display final counts
        $this->displayRowCounts();

        $this->newLine();
        $this->info('✓ Database reseeded successfully!');

        return self::SUCCESS;
    }

    /**
     * Display row counts for all tables.
     */
    private function displayRowCounts(): void
    {
        $tables = [
            'users',
            'products',
            'orders',
            'order_items',
            'payments',
            'refunds',
        ];

        $data = collect($tables)->map(function ($table) {
            try {
                $count = DB::table($table)->count();
                return [
                    ucfirst(str_replace('_', ' ', $table)),
                    number_format($count),
                ];
            } catch (\Exception $e) {
                return [
                    ucfirst(str_replace('_', ' ', $table)),
                    'N/A',
                ];
            }
        });

        $this->table(
            ['Table', 'Row Count'],
            $data
        );
    }
}
