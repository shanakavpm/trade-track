<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('revenue', 12, 2)->default(0);
            $table->integer('order_count')->default(0);
            $table->decimal('sum_total', 12, 2)->default(0);
            $table->decimal('avg_order_value', 12, 2)->default(0);
            $table->timestamps();
            
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_daily');
    }
};
