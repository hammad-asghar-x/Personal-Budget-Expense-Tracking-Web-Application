import { Page } from '@playwright/test';

export class CategoryPage {
  readonly page: Page;
  constructor(page: Page) {
    this.page = page;
  }

  async goto() {
    await this.page.goto('/categories');
  }

  async addCategory(name: string) {
    await this.page.fill('#name', name);
    const submitButton = this.page.locator('form[wire\\:submit="save"] button[type="submit"]');
    await submitButton.waitFor({ state: 'visible', timeout: 10000 });
    await submitButton.click();
    await this.page.waitForSelector(`h4:has-text("${name}")`, { timeout: 15000 });
  }

  categoryRow(name: string) {
    return this.page.locator('h4', { hasText: name });
  }
}
