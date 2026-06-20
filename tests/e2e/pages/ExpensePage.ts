import { Page } from '@playwright/test';

export class ExpensePage {
  readonly page: Page;
  constructor(page: Page) {
    this.page = page;
  }

  async gotoCreate() {
    await this.page.goto('/expenses/create');
  }

  async createExpense({ title, amount, date, category }: { title: string; amount: string; date: string; category?: string }) {
    await this.page.fill('#title', title);
    await this.page.fill('#amount', amount);
    await this.page.fill('#date', date);
    if (category) {
      await this.page.selectOption('#category_id', { label: category });
    }
    await this.page.click('button:has-text("Save Expense")');
  }
}
