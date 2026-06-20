<?php

namespace Tests\Unit\RecurringExpense;

use App\Livewire\ExpenseForm;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RecurringExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_occurrence_date_is_calculated_correctly_monthly(): void
    {
        $expense = Expense::factory()->recurring()->create([
            'recurring_frequency' => 'monthly',
            'recurring_start_date' => '2025-01-15',
            'recurring_end_date' => null,
            'date' => '2025-01-15',
        ]);

        $this->assertEquals('2025-02-15', $expense->getNextOccurrenceDate()->format('Y-m-d'));
    }

    public function test_recurring_expense_stops_generating_after_end_date(): void
    {
        $expense = Expense::factory()->recurring()->create([
            'recurring_start_date' => '2025-01-15',
            'recurring_end_date' => '2025-05-01',
            'date' => '2025-05-01',
        ]);

        $this->assertFalse($expense->shouldGenerateNextOccurrence());
    }

    public function test_recurring_expense_validates_start_date_is_before_end_date(): void
    {
        $form = new ExpenseForm();
        $form->type = 'recurring';
        $form->recurring_frequency = 'monthly';
        $form->recurring_start_date = '2025-06-01';
        $form->recurring_end_date = '2025-05-01';

        $data = [
            'user_id' => User::factory()->create()->id,
            'amount' => 100,
            'title' => 'Test recurring',
            'description' => 'Validation test',
            'date' => '2025-06-01',
            'category_id' => null,
            'type' => 'recurring',
            'recurring_frequency' => 'monthly',
            'recurring_start_date' => '2025-06-01',
            'recurring_end_date' => '2025-05-01',
        ];

        $validator = Validator::make($data, $this->invokeProtectedRules($form));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('recurring_end_date', $validator->errors()->messages());
    }

    public function test_deleting_recurring_template_cascades_to_child_expenses(): void
    {
        $parent = Expense::factory()->recurring()->create([
            'recurring_start_date' => '2025-01-15',
            'recurring_end_date' => null,
            'date' => '2025-01-15',
        ]);

        $child = Expense::factory()->create([
            'parent_expense_id' => $parent->id,
            'user_id' => $parent->user_id,
            'amount' => 100,
            'title' => 'Child expense',
            'date' => '2025-02-15',
        ]);

        $parent->delete();

        $this->assertNotNull($child->fresh()->deleted_at);
    }

    public function test_recurring_expense_frequency_accepts_valid_enums(): void
    {
        $expense = Expense::factory()->recurring()->create([
            'recurring_frequency' => 'weekly',
            'recurring_start_date' => '2025-01-15',
            'recurring_end_date' => null,
            'date' => '2025-01-15',
        ]);

        $this->assertEquals('weekly', $expense->recurring_frequency);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'recurring_frequency' => 'weekly']);
    }

    private function invokeProtectedRules(object $component): array
    {
        $method = new \ReflectionMethod($component, 'rules');
        $method->setAccessible(true);

        return $method->invoke($component);
    }
}
