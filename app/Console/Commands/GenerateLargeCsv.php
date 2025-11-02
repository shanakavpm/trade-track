<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateLargeCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:file 
                            {rows=1000 : Number of rows to generate} 
                            {--filename=file.csv : Output filename in project root}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a large CSV file with realistic order data for testing';

    /**
     * Available products with their details
     *
     * @var array
     */
    protected $products = [
        'LAPTOP-001' => ['name' => 'Premium Laptop X1', 'price' => 1299.99, 'category' => 'Electronics'],
        'PHONE-001'  => ['name' => 'Smartphone Pro', 'price' => 999.99, 'category' => 'Electronics'],
        'TABLET-001' => ['name' => 'Tablet Air', 'price' => 599.99, 'category' => 'Electronics'],
        'WATCH-001'  => ['name' => 'Smart Watch 3', 'price' => 399.99, 'category' => 'Wearables'],
        'HEADPHONE-001' => ['name' => 'Wireless Headphones', 'price' => 349.99, 'category' => 'Audio'],
        'MOUSE-001'  => ['name' => 'Gaming Mouse', 'price' => 89.99, 'category' => 'Accessories'],
        'KEYBOARD-001' => ['name' => 'Mechanical Keyboard', 'price' => 129.99, 'category' => 'Accessories'],
        'MONITOR-001' => ['name' => '27\" 4K Monitor', 'price' => 499.99, 'category' => 'Monitors'],
        'SSD-001'    => ['name' => '1TB SSD', 'price' => 199.99, 'category' => 'Storage'],
        'ROUTER-001' => ['name' => 'WiFi 6 Router', 'price' => 249.99, 'category' => 'Networking']
    ];

    /**
     * Customer IDs and their details
     *
     * @var array
     */
    protected $customers = [
        1 => ['name' => 'John Doe', 'email' => 'john@example.com'],
        2 => ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
        3 => ['name' => 'Bob Johnson', 'email' => 'bob@example.com'],
        4 => ['name' => 'Alice Williams', 'email' => 'alice@example.com'],
        5 => ['name' => 'Charlie Brown', 'email' => 'charlie@example.com']
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->option('filename');
        $rows = (int)$this->argument('rows');
        $path = base_path($filename);

        $this->info("Generating {$rows} realistic order records in {$filename}...");

        $handle = fopen($path, 'w');
        
        // Write headers - matching the expected format for orders:import
        fputcsv($handle, ['order_id', 'customer_id', 'sku', 'qty', 'unit_price']);

        $productSkus = array_keys($this->products);
        $customerIds = array_keys($this->customers);
        $startDate = Carbon::now()->subYear();
        $endDate = Carbon::now();

        // Generate rows
        for ($i = 1; $i <= $rows; $i++) {
            $productSku = $productSkus[array_rand($productSkus)];
            $customerId = $customerIds[array_rand($customerIds)];
            
            // Generate random order date within the past year
            $orderDate = $this->randomDateInRange($startDate, $endDate);
            
            $orderId = 'ORD-' . $orderDate->format('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT);
            $qty = rand(1, 5);

            fputcsv($handle, [
                $orderId,
                $customerId,
                $productSku,
                $qty,
                $this->products[$productSku]['price']
            ]);
        }

        fclose($handle);

        $this->info("Successfully generated {$filename} with {$rows} realistic order records");
        $this->info("File location: {$path}");
        $this->info("Products included: " . count($this->products));
        $this->info("Customers: " . count($this->customers));
    }
    
    /**
     * Generate a random date between two dates
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return \Carbon\Carbon
     */
    private function randomDateInRange($startDate, $endDate)
    {
        $randomTimestamp = mt_rand($startDate->timestamp, $endDate->timestamp);
        return Carbon::createFromTimestamp($randomTimestamp);
    }
}
