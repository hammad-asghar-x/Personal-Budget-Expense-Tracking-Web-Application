<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HammadAsgharSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'hammad.asghar@student.pk'],
            [
                'name' => 'Hammad Asghar',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        );

        $this->clearUserData($user->id);

        $categories = $this->createCategories($user->id);
        $this->createBudgets($user->id, $categories);
        $this->createExpenses($user->id, $categories);
        $this->createRecurringExpenses($user->id, $categories);
    }

    private function clearUserData(int $userId): void
    {
        Expense::where('user_id', $userId)->forceDelete();
        Budget::where('user_id', $userId)->delete();
        Category::where('user_id', $userId)->delete();
    }

    private function createCategories(int $userId): array
    {
        $definitions = [
            ['name' => 'Food & Chai', 'color' => '#F59E0B', 'icon' => 'utensils'],
            ['name' => 'Transport', 'color' => '#3B82F6', 'icon' => 'car'],
            ['name' => 'University', 'color' => '#8B5CF6', 'icon' => 'academic-cap'],
            ['name' => 'Mobile & Internet', 'color' => '#10B981', 'icon' => 'device-phone-mobile'],
            ['name' => 'Hostel & Rent', 'color' => '#EF4444', 'icon' => 'home'],
            ['name' => 'Entertainment', 'color' => '#EC4899', 'icon' => 'film'],
            ['name' => 'Books & Stationery', 'color' => '#6366F1', 'icon' => 'book-open'],
        ];

        $categories = [];
        foreach ($definitions as $definition) {
            $categories[$definition['name']] = Category::create([
                'user_id' => $userId,
                ...$definition,
            ]);
        }

        return $categories;
    }

    private function createBudgets(int $userId, array $categories): void
    {
        $month = now()->month;
        $year = now()->year;

        $budgets = [
            ['category' => 'Food & Chai', 'amount' => 12000],
            ['category' => 'Transport', 'amount' => 5000],
            ['category' => 'University', 'amount' => 25000],
            ['category' => 'Mobile & Internet', 'amount' => 2500],
            ['category' => 'Hostel & Rent', 'amount' => 18000],
            ['category' => 'Entertainment', 'amount' => 4000],
            ['category' => 'Books & Stationery', 'amount' => 3000],
        ];

        foreach ($budgets as $budget) {
            Budget::create([
                'user_id' => $userId,
                'category_id' => $categories[$budget['category']]->id,
                'amount' => $budget['amount'],
                'month' => $month,
                'year' => $year,
            ]);
        }

        Budget::create([
            'user_id' => $userId,
            'category_id' => null,
            'amount' => 70000,
            'month' => $month,
            'year' => $year,
        ]);
    }

    private function createExpenses(int $userId, array $categories): void
    {
        $expenses = [
            ['category' => 'Food & Chai', 'title' => 'Biryani with friends - Saddar', 'amount' => 850, 'date' => '2026-05-02'],
            ['category' => 'Food & Chai', 'title' => 'Chai & paratha - campus canteen', 'amount' => 320, 'date' => '2026-05-05'],
            ['category' => 'Food & Chai', 'title' => 'Groceries - general store', 'amount' => 2400, 'date' => '2026-05-08'],
            ['category' => 'Food & Chai', 'title' => 'Zinger burger - Food Street', 'amount' => 650, 'date' => '2026-05-12'],
            ['category' => 'Transport', 'title' => 'Careem to university', 'amount' => 380, 'date' => '2026-05-03'],
            ['category' => 'Transport', 'title' => 'Metro bus card top-up', 'amount' => 1500, 'date' => '2026-05-07'],
            ['category' => 'Transport', 'title' => 'Rickshaw - hostel to campus', 'amount' => 250, 'date' => '2026-05-14'],
            ['category' => 'University', 'title' => 'Semester lab fee', 'amount' => 4500, 'date' => '2026-05-01'],
            ['category' => 'University', 'title' => 'Photocopies & printouts', 'amount' => 450, 'date' => '2026-05-10'],
            ['category' => 'Mobile & Internet', 'title' => 'Jazz monthly package', 'amount' => 1200, 'date' => '2026-05-01'],
            ['category' => 'Hostel & Rent', 'title' => 'Hostel mess charges', 'amount' => 8000, 'date' => '2026-05-01'],
            ['category' => 'Entertainment', 'title' => 'Cricket match snacks', 'amount' => 500, 'date' => '2026-05-06'],
            ['category' => 'Entertainment', 'title' => 'Netflix share with roommate', 'amount' => 400, 'date' => '2026-05-09'],
            ['category' => 'Books & Stationery', 'title' => 'AST course notes printing', 'amount' => 600, 'date' => '2026-05-11'],
            ['category' => 'Books & Stationery', 'title' => 'Notebook & pens', 'amount' => 350, 'date' => '2026-05-15'],
            ['category' => 'Food & Chai', 'title' => 'Daal chawal lunch', 'amount' => 280, 'date' => '2026-04-18'],
            ['category' => 'Transport', 'title' => 'Bus pass April', 'amount' => 1500, 'date' => '2026-04-05'],
            ['category' => 'University', 'title' => 'Assignment binding', 'amount' => 300, 'date' => '2026-04-22'],
            ['category' => 'Food & Chai', 'title' => 'Samosa & chai break', 'amount' => 200, 'date' => '2026-03-12'],
            ['category' => 'Mobile & Internet', 'title' => 'Internet cafe session', 'amount' => 150, 'date' => '2026-03-20'],
            ['category' => 'Hostel & Rent', 'title' => 'March hostel fee', 'amount' => 15000, 'date' => '2026-03-01'],
        ];

        foreach ($expenses as $expense) {
            Expense::create([
                'user_id' => $userId,
                'category_id' => $categories[$expense['category']]->id,
                'amount' => $expense['amount'],
                'title' => $expense['title'],
                'description' => null,
                'date' => $expense['date'],
                'type' => 'one-time',
            ]);
        }
    }

    private function createRecurringExpenses(int $userId, array $categories): void
    {
        Expense::create([
            'user_id' => $userId,
            'category_id' => $categories['Hostel & Rent']->id,
            'amount' => 15000,
            'title' => 'Monthly hostel rent',
            'description' => 'Paid at start of each month',
            'date' => now()->startOfMonth(),
            'type' => 'recurring',
            'recurring_frequency' => 'monthly',
            'recurring_start_date' => Carbon::parse('2026-01-01'),
            'recurring_end_date' => Carbon::parse('2026-12-31'),
        ]);

        Expense::create([
            'user_id' => $userId,
            'category_id' => $categories['Mobile & Internet']->id,
            'amount' => 1200,
            'title' => 'Jazz Super package',
            'description' => 'Monthly mobile bundle',
            'date' => now()->startOfMonth(),
            'type' => 'recurring',
            'recurring_frequency' => 'monthly',
            'recurring_start_date' => Carbon::parse('2026-01-01'),
            'recurring_end_date' => null,
        ]);

        Expense::create([
            'user_id' => $userId,
            'category_id' => $categories['Food & Chai']->id,
            'amount' => 350,
            'title' => 'Weekly chai & snacks',
            'description' => 'Regular canteen visit',
            'date' => now()->startOfWeek(),
            'type' => 'recurring',
            'recurring_frequency' => 'weekly',
            'recurring_start_date' => Carbon::parse('2026-05-01'),
            'recurring_end_date' => Carbon::parse('2026-08-31'),
        ]);
    }
}
