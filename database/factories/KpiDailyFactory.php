<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KpiDailyFactory extends Factory
{
    public function definition(): array
    {
        $orderCount = $this->faker->numberBetween(1, 100);
        $revenue = $this->faker->randomFloat(2, 100, 10000);
        $averageOrderValue = $revenue / $orderCount;

        return [
            'date' => $this->faker->date(),
            'revenue' => $revenue,
            'order_count' => $orderCount,
            'average_order_value' => $averageOrderValue,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
