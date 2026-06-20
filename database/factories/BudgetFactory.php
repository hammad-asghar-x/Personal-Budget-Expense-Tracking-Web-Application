<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'amount' => fake()->randomFloat(2, 100, 1000),
            'month' => now()->month,
            'year' => now()->year,
        ];
    }

    public function forCategory(Category $category): static
    {
        return $this->state(fn () => [
            'user_id' => $category->user_id,
            'category_id' => $category->id,
        ]);
    }
}
