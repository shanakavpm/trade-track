<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->decimal('total', 12, 2);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('customer_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
