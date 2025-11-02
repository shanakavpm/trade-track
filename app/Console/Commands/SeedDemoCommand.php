<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed database with demo data (without dropping tables)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('═══════════════════════════════════════');
        $this->info('  Demo Data Seeding');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        $this->warn('This will add demo data to your existing database.');
        $this->warn('Existing data will NOT be deleted.');
        $this->newLine();

        if (!$this->confirm('Do you want to continue?', true)) {
            $this->info('Operation cancelled.');
            return self::FAILURE;
        }

        // Set reduced counts for demo
        putenv('SEED_USERS=10');
        putenv('SEED_PRODUCTS=20');
        putenv('SEED_ORDERS=30');

        $this->info('Seeding demo data...');
        $this->info('  - Users: 10');
        $this->info('  - Products: 20');
        $this->info('  - Orders: 30');
        $this->newLine();

        Artisan::call('db:seed', [], $this->getOutput());

        $this->newLine();
        $this->info('✓ Demo data seeded successfully!');

        return self::SUCCESS;
    }
}
