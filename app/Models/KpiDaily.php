<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiDaily extends Model
{
    protected $table = 'kpi_daily';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'revenue' => 'decimal:2',
            'order_count' => 'integer',
            'sum_total' => 'decimal:2',
            'avg_order_value' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
