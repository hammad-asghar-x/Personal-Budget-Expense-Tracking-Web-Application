<?php

namespace Tests\Unit\Expense;

use App\Livewire\ExpenseForm;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    /** E1: Recurring type — isRecurring true (positive). */
    public function test_is_recurring_returns_true_for_recurring_type(): void
    {
        $expense = Expense::factory()->recurring()->make();

        $this->assertTrue($expense->isRecurring());
    }

    /** E2: One-time type — isRecurring false (negative). */
    public function test_is_recurring_returns_false_for_one_time_type(): void
    {
        $expense = Expense::factory()->make(['type' => 'one-time']);

        $this->assertFalse($expense->isRecurring());
    }

    /** E3: scopeForUser returns only that user's expenses (positive). */
    public function test_scope_for_user_filters_by_user_id(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Expense::factory()->create(['user_id' => $userA->id]);
        Expense::factory()->create(['user_id' => $userA->id]);
        Expense::factory()->create(['user_id' => $userB->id]);

        $this->assertCount(2, Expense::forUser($userA->id)->get());
    }

    /** E4: Recurring with no end date — should generate next occurrence (positive). */
    public function test_should_generate_next_occurrence_when_recurring_without_end_date(): void
    {
        $expense = Expense::factory()->recurring()->create([
            'recurring_end_date' => null,
        ]);

        $this->assertTrue($expense->shouldGenerateNextOccurrence());
    }

    /** E5: Recurring with past end date — should not generate (negative). */
    public function test_should_not_generate_next_occurrence_when_end_date_passed(): void
    {
        $expense = Expense::factory()->recurring()->create([
            'recurring_end_date' => now()->subDay(),
        ]);

        $this->assertFalse($expense->shouldGenerateNextOccurrence());
    }

    /** E6: Expense creation fails if amount is zero or negative (negative). */
    public function test_expense_creation_fails_if_amount_is_zero_or_negative(): void
    {
        $form = new ExpenseForm();
        $data = [
            'user_id' => User::factory()->create()->id,
            'amount' => 0,
            'title' => 'Test expense',
            'description' => null,
            'date' => now()->format('Y-m-d'),
            'type' => 'one-time',
        ];

        $validator = Validator::make($data, $this->invokeProtectedRules($form));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());

        $data['amount'] = -10;
        $validator = Validator::make($data, $this->invokeProtectedRules($form));
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }

    /** E7: Expense soft delete sets deleted_at timestamp (positive). */
    public function test_expense_soft_delete_sets_deleted_at_timestamp(): void
    {
        $expense = Expense::factory()->create();

        $expense->delete();

        $this->assertNotNull($expense->fresh()->deleted_at);
        $this->assertNotNull(Expense::withTrashed()->find($expense->id));
    }

    /** E8: Expense retrieval excludes soft-deleted records (positive). */
    public function test_expense_retrieval_excludes_soft_deleted_records(): void
    {
        $expense = Expense::factory()->create();
        $expense->delete();

        $this->assertCount(0, Expense::all());
        $this->assertCount(1, Expense::withTrashed()->get());
    }

    /** E9: Expense creation fails if title is empty (negative). */
    public function test_expense_creation_fails_if_title_is_empty(): void
    {
        $form = new ExpenseForm();
        $data = [
            'user_id' => User::factory()->create()->id,
            'amount' => 10,
            'title' => '',
            'description' => null,
            'date' => now()->format('Y-m-d'),
            'type' => 'one-time',
        ];

        $validator = Validator::make($data, $this->invokeProtectedRules($form));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->messages());
    }

    /** E10: Expense date defaults to today if not provided (positive). */
    public function test_expense_date_defaults_to_today_if_not_provided(): void
    {
        $user = User::factory()->create();

        $expense = Expense::create([
            'user_id' => $user->id,
            'category_id' => null,
            'amount' => 42.50,
            'title' => 'Default date expense',
            'description' => null,
            'type' => 'one-time',
        ]);

        $this->assertEquals(now()->format('Y-m-d'), $expense->date->format('Y-m-d'));
    }

    private function invokeProtectedRules(object $component): array
    {
        $method = new \ReflectionMethod($component, 'rules');
        $method->setAccessible(true);

        return $method->invoke($component);
    }
}
