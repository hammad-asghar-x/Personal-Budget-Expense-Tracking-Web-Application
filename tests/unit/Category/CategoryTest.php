<?php

namespace Tests\Unit\Category;

use App\Livewire\Categories;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    /** C1: Expenses in target month sum correctly (positive). */
    public function test_get_total_spent_for_month_sums_expenses(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 100,
            'date' => '2025-06-05',
        ]);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 250,
            'date' => '2025-06-20',
        ]);

        $this->assertEquals(350.0, $this->category->getTotalSpentForMonth(6, 2025));
    }

    /** C2: No expenses — total is zero (negative). */
    public function test_get_total_spent_for_month_returns_zero_with_no_expenses(): void
    {
        $this->assertEquals(0.0, $this->category->getTotalSpentForMonth(6, 2025));
    }

    /** C3: Expenses in another month are excluded (negative). */
    public function test_get_total_spent_for_month_excludes_other_months(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 500,
            'date' => '2025-05-15',
        ]);

        $this->assertEquals(0.0, $this->category->getTotalSpentForMonth(6, 2025));
    }

    /** C4: Multiple expenses in same month sum correctly (positive). */
    public function test_get_total_spent_for_month_with_multiple_entries(): void
    {
        foreach ([50.0, 75.25, 24.75] as $amount) {
            Expense::factory()->create([
                'user_id' => $this->user->id,
                'category_id' => $this->category->id,
                'amount' => $amount,
                'date' => '2025-06-10',
            ]);
        }

        $this->assertEquals(150.0, $this->category->getTotalSpentForMonth(6, 2025));
    }

    /** C5: Another category's expenses are not included (negative). */
    public function test_get_total_spent_for_month_excludes_other_categories(): void
    {
        $otherCategory = Category::factory()->create(['user_id' => $this->user->id]);

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $otherCategory->id,
            'amount' => 999,
            'date' => '2025-06-12',
        ]);

        $this->assertEquals(0.0, $this->category->getTotalSpentForMonth(6, 2025));
    }

    /** C6: Category name must be unique per user (negative). */
    public function test_category_name_must_be_unique_per_user(): void
    {
        $existing = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Groceries',
        ]);

        $this->actingAs($this->user);

        $component = new Categories();
        $component->name = 'Groceries';
        $component->color = '#10B981';

        $validator = Validator::make([
            'name' => $component->name,
            'color' => $component->color,
            'icon' => $component->icon,
        ], $this->invokeProtectedRules($component));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->messages());
    }

    /** C7: Category cannot be deleted if it has linked expenses (negative). */
    public function test_category_cannot_be_deleted_if_it_has_linked_expenses(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete category with existing expenses.');

        $this->category->delete();
    }

    /** C8: Category can be deleted if it has no expenses (positive). */
    public function test_category_can_be_deleted_if_it_has_no_expenses(): void
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $category->delete();

        $this->assertNull(Category::find($category->id));
    }

    /** C9: Category creation fails if color is missing (negative). */
    public function test_category_creation_fails_if_color_is_missing(): void
    {
        $this->actingAs($this->user);

        $component = new Categories();
        $component->name = 'Utilities';
        $component->color = '';

        $validator = Validator::make([
            'name' => $component->name,
            'color' => $component->color,
            'icon' => $component->icon,
        ], $this->invokeProtectedRules($component));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('color', $validator->errors()->messages());
    }

    /** C10: User retrieves only their own categories (positive). */
    public function test_user_retrieves_only_their_own_categories(): void
    {
        $otherUser = User::factory()->create();
        Category::factory()->create(['user_id' => $otherUser->id]);
        Category::factory()->create(['user_id' => $otherUser->id]);

        $categories = Category::forUser($this->user->id)->get();

        $this->assertNotEmpty($categories);
        $this->assertTrue($categories->every(fn ($category) => $category->user_id === $this->user->id));
    }

    private function invokeProtectedRules(object $component): array
    {
        $method = new \ReflectionMethod($component, 'rules');
        $method->setAccessible(true);

        return $method->invoke($component);
    }
}
