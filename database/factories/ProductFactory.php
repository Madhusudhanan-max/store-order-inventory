<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(2, true)),
            'code' => strtoupper($this->faker->unique()->bothify('PRD-####')),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'tax_percentage' => $this->faker->randomElement([0, 5, 12, 18]),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function lowStock(int $max = 5): self
    {
        return $this->state(fn () => ['stock' => $this->faker->numberBetween(0, $max)]);
    }
}
