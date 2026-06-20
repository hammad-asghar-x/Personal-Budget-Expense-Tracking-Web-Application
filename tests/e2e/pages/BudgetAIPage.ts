import { Page } from '@playwright/test';

export class BudgetAIPage {
  readonly page: Page;
  constructor(page: Page) {
    this.page = page;
  }

  async gotoBudgetAI() {
    await this.page.goto('/budgets/create');
  }

  async selectBudgetPeriod(month: string, year: string) {
    await this.page.selectOption('#month', month);
    await this.page.selectOption('#year', year);
    await this.page.waitForSelector('button:has-text("✨ Get AI Suggestion")', { timeout: 10000 });
  }

  async requestRecommendation(categoryLabel?: string) {
    if (categoryLabel) {
      await this.page.selectOption('#category_id', { label: categoryLabel });
    }
    const recommendationButton = this.page.getByRole('button', { name: /Get AI Suggestion/i });
    await recommendationButton.waitFor({ state: 'visible', timeout: 10000 });
    await recommendationButton.click();
    await this.page.waitForSelector('text=Analyzing...', { timeout: 30000 });
  }

  async waitForRecommendationOutcome() {
    await Promise.any([
      this.page.waitForSelector('text=AI Recommendation', { timeout: 60000 }),
      this.page.waitForSelector('text=Unable to generate recommendation', { timeout: 60000 }),
      this.page.waitForSelector('text=Ai service temporarily unavailable', { timeout: 60000 }),
      this.page.waitForSelector('button:has-text("✨ Get AI Suggestion") >> [disabled]', { timeout: 60000 }),
    ]);
  }
}
