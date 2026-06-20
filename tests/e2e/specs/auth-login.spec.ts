import { test, expect } from '@playwright/test';

test.describe('Auth Login UI', () => {
  test('login page loads with form', async ({ page }) => {
    await page.goto('/login');
    
    await expect(page.locator('input[type="email"]')).toBeVisible();
    await expect(page.locator('input[type="password"]')).toBeVisible();
    await expect(page.getByRole('button', { name: /Log in/i })).toBeVisible();
  });

  test('user can login with valid credentials', async ({ page }) => {
    await page.goto('/login');
    
    await page.fill('input[type="email"]', 'hammad.asghar@student.pk');
    await page.fill('input[type="password"]', 'password');
    await page.getByRole('button', { name: /Log in/i }).click();
    
    // Should redirect to dashboard
    await page.waitForURL('**/dashboard', { timeout: 10000 });
    await expect(page).toHaveURL(/\/dashboard/);
  });

  test('login fails with invalid email', async ({ page }) => {
    await page.goto('/login');
    
    await page.fill('input[type="email"]', 'wrong@example.com');
    await page.fill('input[type="password"]', 'password');
    await page.getByRole('button', { name: /Log in/i }).click();
    
    // Should stay on login page
    await expect(page).toHaveURL(/\/login/);
  });

  test('login fails with invalid password', async ({ page }) => {
    await page.goto('/login');
    
    await page.fill('input[type="email"]', 'hammad.asghar@student.pk');
    await page.fill('input[type="password"]', 'wrongpassword');
    await page.getByRole('button', { name: /Log in/i }).click();
    
    // Should stay on login page
    await expect(page).toHaveURL(/\/login/);
  });

  test('login requires email and password', async ({ page }) => {
    await page.goto('/login');
    
    await page.getByRole('button', { name: /Log in/i }).click();
    
    // Should stay on login page
    await expect(page).toHaveURL(/\/login/);
  });
});
