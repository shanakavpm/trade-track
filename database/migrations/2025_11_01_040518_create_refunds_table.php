<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->unique();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('payment_id');
            $table->index('status');
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
