<?php

namespace App\Console\Commands;

use App\Jobs\ValidateRowJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class OrdersImportCommand extends Command
{
    protected $signature = 'orders:import {file} {--chunk=100 : Number of rows to process in each chunk} {--queue=default : Queue connection to use}';
    protected $description = 'Import orders from CSV file with chunked processing';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        // Try Storage first, then fall back to direct file path
        if (Storage::exists($filePath)) {
            $fullPath = Storage::path($filePath);
        } elseif (file_exists($filePath)) {
            $fullPath = $filePath;
        } elseif (file_exists(storage_path('app/' . $filePath))) {
            $fullPath = storage_path('app/' . $filePath);
        } else {
            $this->error("File not found: {$filePath}");
            $this->error("Tried paths:");
            $this->error("  - Storage: " . Storage::path($filePath));
            $this->error("  - Direct: " . $filePath);
            $this->error("  - Storage app: " . storage_path('app/' . $filePath));
            return self::FAILURE;
        }

        try {
            $csv = Reader::createFromPath($fullPath, 'r');
            $csv->setHeaderOffset(0);

            $expectedHeaders = ['order_id', 'customer_id', 'sku', 'qty', 'unit_price'];
            $headers = $csv->getHeader();

            if ($headers !== $expectedHeaders) {
                $this->error('Invalid CSV headers. Expected: ' . implode(', ', $expectedHeaders));
                return self::FAILURE;
            }

            $this->info('Starting CSV import...');
            $rowNumber = 1; // Start after header
            $chunkSize = (int)$this->option('chunk');
            $queue = $this->option('queue');
            $chunk = [];

            foreach ($csv->getRecords() as $record) {
                $rowNumber++;
                $chunk[] = ['data' => $record, 'row' => $rowNumber];

                if (count($chunk) >= $chunkSize) {
                    $this->dispatchChunk($chunk, $queue);
                    $chunk = [];
                }
            }

            // Dispatch remaining rows
            if (!empty($chunk)) {
                $this->dispatchChunk($chunk, $queue);
            }

            $this->info("CSV import queued successfully. Total rows: " . ($rowNumber - 1));
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function dispatchChunk(array $chunk, string $queue): void
    {
        foreach ($chunk as $item) {
            $row = $item['data'];
            $rowNumber = $item['row'];
            ValidateRowJob::dispatch($row, $rowNumber)->onQueue($queue);
        }
    }
}
