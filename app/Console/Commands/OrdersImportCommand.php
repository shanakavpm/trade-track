<?php

namespace App\Console\Commands;

use App\Jobs\ValidateRowJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class OrdersImportCommand extends Command
{
    protected $signature = 'orders:import {file}';
    protected $description = 'Import orders from CSV file with chunked processing';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!Storage::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $fullPath = Storage::path($filePath);

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
            $chunkSize = 100;
            $chunk = [];

            foreach ($csv->getRecords() as $record) {
                $rowNumber++;
                $chunk[] = ['data' => $record, 'row' => $rowNumber];

                if (count($chunk) >= $chunkSize) {
                    $this->dispatchChunk($chunk);
                    $chunk = [];
                }
            }

            // Dispatch remaining rows
            if (!empty($chunk)) {
                $this->dispatchChunk($chunk);
            }

            $this->info("CSV import queued successfully. Total rows: " . ($rowNumber - 1));
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function dispatchChunk(array $chunk): void
    {
        foreach ($chunk as $item) {
            ValidateRowJob::dispatch($item['data'], $item['row']);
        }
    }
}
