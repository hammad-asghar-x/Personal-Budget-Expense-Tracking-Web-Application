import { Page } from '@playwright/test';

export async function login(page: Page, email: string, password: string) {
  await page.goto('/login');
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.locator('button[data-test="login-button"]').click();
  await Promise.race([
    page.waitForURL('**/dashboard', { timeout: 20000 }),
    page.waitForSelector('text=Invalid', { timeout: 20000 }).then(() => { throw new Error('Login failed: invalid credentials'); }),
  ]);
}
