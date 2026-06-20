<?php

namespace Tests\Unit\Budget;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
    }

    /** B1: Spent less than budget — not over budget (positive). */
    public function test_is_not_over_budget_when_spent_is_less_than_amount(): void
    {
        $budget = $this->createBudget(1000);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'amount' => 400,
            'date' => '2025-06-15',
        ]);

        $this->assertFalse($budget->isOverBudget());
    }

    /** B2: Spent more than budget — over budget (positive). */
    public function test_is_over_budget_when_spent_exceeds_amount(): void
    {
        $budget = $this->createBudget(500);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'amount' => 750,
            'date' => '2025-06-10',
        ]);

        $this->assertTrue($budget->isOverBudget());
    }

    /** B3: Remaining amount equals budget minus spent (positive). */
    public function test_get_remaining_amount_returns_budget_minus_spent(): void
    {
        $budget = $this->createBudget(1000);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'amount' => 300,
            'date' => '2025-06-05',
        ]);

        $this->assertEquals(700.0, $budget->getRemainingAmount());
    }

    /** B4: Category budget only counts that category's expenses (positive). */
    public function test_get_spent_amount_only_includes_matching_category(): void
    {
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 1000,
            'month' => 6,
            'year' => 2025,
        ]);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 200,
            'date' => '2025-06-01',
        ]);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'amount' => 500,
            'date' => '2025-06-02',
        ]);

        $this->assertEquals(200.0, $budget->getSpentAmount());
    }

    /** B5: No expenses in month — spent is zero (negative). */
    public function test_get_spent_amount_is_zero_when_no_expenses(): void
    {
        $budget = $this->createBudget(800);

        $this->assertEquals(0.0, $budget->getSpentAmount());
    }

    /** B6: Budget creation fails if amount is zero or negative (negative). */
    public function test_budget_creation_fails_if_amount_is_zero_or_negative(): void
    {
        $this->actingAs($this->user);

        $budget = new \App\Livewire\BudgetForm();
        $budget->month = 6;
        $budget->year = 2025;

        $validator = \Illuminate\Support\Facades\Validator::make([
            'amount' => 0,
            'month' => $budget->month,
            'year' => $budget->year,
            'category_id' => null,
        ], $this->invokeProtectedRules($budget));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());

        $validator = \Illuminate\Support\Facades\Validator::make([
            'amount' => -1,
            'month' => $budget->month,
            'year' => $budget->year,
            'category_id' => null,
        ], $this->invokeProtectedRules($budget));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }

    /** B7: Duplicate budget for same month/year/category is prevented (negative). */
    public function test_duplicate_budget_for_same_month_year_category_is_prevented(): void
    {
        $this->actingAs($this->user);

        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 300,
            'month' => 6,
            'year' => 2025,
        ]);

        $component = new \App\Livewire\BudgetForm();
        $component->month = 6;
        $component->year = 2025;
        $component->category_id = $this->category->id;

        $validator = \Illuminate\Support\Facades\Validator::make([
            'amount' => 500,
            'month' => $component->month,
            'year' => $component->year,
            'category_id' => $component->category_id,
        ], $this->invokeProtectedRules($component));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->messages());
    }

    /** B8: Budget correctly calculates spent amount across uncategorized expenses (positive). */
    public function test_get_spent_amount_for_uncategorized_expenses(): void
    {
        $budget = $this->createBudget(1000);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'amount' => 150,
            'date' => '2025-06-05',
        ]);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'amount' => 250,
            'date' => '2025-06-10',
        ]);

        $this->assertEquals(400.0, $budget->getSpentAmount());
    }

    /** B9: Budget remaining is exactly 0 when spent equals budget (boundary). */
    public function test_budget_remaining_is_zero_when_spent_equals_budget(): void
    {
        $budget = $this->createBudget(500);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'amount' => 500,
            'date' => '2025-06-12',
        ]);

        $this->assertEquals(0.0, $budget->getRemainingAmount());
    }

    /** B10: Budget remaining is negative when over budget (boundary). */
    public function test_budget_remaining_is_negative_when_over_budget(): void
    {
        $budget = $this->createBudget(500);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'amount' => 600,
            'date' => '2025-06-12',
        ]);

        $this->assertEquals(-100.0, $budget->getRemainingAmount());
    }

    private function createBudget(float $amount): Budget
    {
        return Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'amount' => $amount,
            'month' => 6,
            'year' => 2025,
        ]);
    }

    private function invokeProtectedRules(object $component): array
    {
        $method = new \ReflectionMethod($component, 'rules');
        $method->setAccessible(true);

        return $method->invoke($component);
    }
}
