<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['email', 'log'])->default('email');
            $table->string('status');
            $table->decimal('total', 12, 2);
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('customer_id');
            $table->index('sent_at');
            $table->index(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
