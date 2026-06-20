<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'amount' => fake()->randomFloat(2, 10, 500),
            'title' => fake()->words(3, true),
            'description' => null,
            'date' => now(),
            'type' => 'one-time',
            'recurring_frequency' => null,
            'recurring_start_date' => null,
            'recurring_end_date' => null,
            'parent_expense_id' => null,
            'is_auto_generated' => false,
        ];
    }

    public function recurring(): static
    {
        return $this->state(fn () => [
            'type' => 'recurring',
            'recurring_frequency' => 'monthly',
            'recurring_start_date' => now()->subMonth(),
            'recurring_end_date' => null,
        ]);
    }

    public function forUserAndCategory(User $user, ?Category $category = null): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'category_id' => $category?->id,
        ]);
    }
}
