<?php

namespace Tests\Unit\BudgetAIService;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Services\BudgetAIService;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BudgetAIServiceTest extends TestCase
{
    use RefreshDatabase;

    private BudgetAIService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BudgetAIService();
        $this->user = User::factory()->create();
    }

    /** AI1: More than 5 expenses in last 3 months — has enough data (positive). */
    public function test_has_enough_historical_data_when_more_than_five_expenses(): void
    {
        foreach (range(1, 6) as $day) {
            Expense::factory()->create([
                'user_id' => $this->user->id,
                'date' => now()->subDays($day),
            ]);
        }

        $this->assertTrue($this->service->hasEnoughHistoricalData(null, $this->user->id));
    }

    /** AI2: Four or fewer expenses — not enough data (negative). */
    public function test_has_not_enough_historical_data_when_four_or_fewer_expenses(): void
    {
        foreach (range(1, 4) as $day) {
            Expense::factory()->create([
                'user_id' => $this->user->id,
                'date' => now()->subDays($day),
            ]);
        }

        $this->assertFalse($this->service->hasEnoughHistoricalData(null, $this->user->id));
    }

    /** AI3: No spending history before target month — recommendation is null (negative). */
    public function test_get_budget_recommendation_returns_null_without_history(): void
    {
        $result = $this->service->getBudgetRecommendation(null, $this->user->id, 6, 2025);

        $this->assertNull($result);
    }

    /** AI4: Valid JSON from Gemini — parsed recommendation (positive). */
    public function test_get_budget_recommendation_parses_valid_gemini_json(): void
    {
        $this->seedHistoricalExpenses();

        Gemini::fake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => '{"recommended": 500, "min": 450, "max": 550, "explanation": "Based on trends.", "tip": "Track weekly."}',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $result = $this->service->getBudgetRecommendation(null, $this->user->id, 6, 2025);

        $this->assertNotNull($result);
        $this->assertEquals(500.0, $result['recommended']);
        $this->assertEquals(450.0, $result['min']);
        $this->assertEquals(550.0, $result['max']);
    }

    /** AI5: Invalid Gemini response — fallback recommendation (negative). */
    public function test_get_budget_recommendation_uses_fallback_on_invalid_response(): void
    {
        $this->seedHistoricalExpenses();

        Gemini::fake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Sorry, I cannot format JSON right now.'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $result = $this->service->getBudgetRecommendation(null, $this->user->id, 6, 2025);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('recommended', $result);
        $this->assertGreaterThan(0, $result['recommended']);
        $this->assertArrayHasKey('explanation', $result);
    }

    /** AI6: AI service handles API timeout gracefully (negative). */
    public function test_ai_service_handles_api_timeout_gracefully(): void
    {
        $this->seedHistoricalExpenses();

        Gemini::fake([
            GenerateContentResponse::fake([ 'status' => 504, 'text' => fn () => throw new \Exception('Timeout') ]),
        ]);

        $result = $this->service->getBudgetRecommendation(null, $this->user->id, 6, 2025);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('recommended', $result);
        $this->assertArrayHasKey('explanation', $result);
    }

    /** AI7: AI service handles empty API response (negative). */
    public function test_ai_service_handles_empty_api_response(): void
    {
        $this->seedHistoricalExpenses();

        Gemini::fake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => ''],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $result = $this->service->getBudgetRecommendation(null, $this->user->id, 6, 2025);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('recommended', $result);
        $this->assertArrayHasKey('explanation', $result);
    }

    /** AI8: Historical data filter correctly ignores future dates (boundary). */
    public function test_historical_data_filter_ignores_future_dates(): void
    {
        foreach (range(1, 5) as $day) {
            Expense::factory()->create([
                'user_id' => $this->user->id,
                'date' => now()->subDays($day),
                'amount' => 50,
            ]);
        }

        Expense::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->addDays(10),
            'amount' => 100,
        ]);

        $this->assertTrue($this->service->hasEnoughHistoricalData(null, $this->user->id));
    }

    private function seedHistoricalExpenses(): void
    {
        foreach (['2025-05-15', '2025-04-15', '2025-03-15'] as $date) {
            Expense::factory()->create([
                'user_id' => $this->user->id,
                'amount' => 100,
                'date' => $date,
            ]);
        }
    }
}
