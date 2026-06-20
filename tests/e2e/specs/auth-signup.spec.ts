import { test, expect } from '@playwright/test';

test.describe('Auth Sign Up UI', () => {
  test('signup page loads with form', async ({ page }) => {
    await page.goto('/register');
    
    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[type="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
    await expect(page.getByRole('button', { name: /Sign up|Create account/i })).toBeVisible();
  });

  test('user can signup with valid data', async ({ page }) => {
    const timestamp = Date.now();
    const email = `test${timestamp}@example.com`;
    
    await page.goto('/register');
    
    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[type="email"]', email);
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');
    
    await page.getByRole('button', { name: /Sign up|Create account/i }).click();
    
    // Should redirect to dashboard or verify email
    await page.waitForURL(/\/(dashboard|verify-email)/, { timeout: 10000 });
    await expect(page).toHaveURL(/\/(dashboard|verify-email)/);
  });

  test('signup fails with mismatched passwords', async ({ page }) => {
    const timestamp = Date.now();
    const email = `test${timestamp}@example.com`;
    
    await page.goto('/register');
    
    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[type="email"]', email);
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password456');
    
    await page.getByRole('button', { name: /Sign up|Create account/i }).click();
    
    // Should stay on signup page or show error
    await expect(page).toHaveURL(/\/register/);
  });

  test('signup fails with existing email', async ({ page }) => {
    await page.goto('/register');
    
    await page.fill('input[name="name"]', 'Another User');
    await page.fill('input[type="email"]', 'hammad.asghar@student.pk');
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');
    
    await page.getByRole('button', { name: /Sign up|Create account/i }).click();
    
    // Should stay on signup page or show error
    await expect(page).toHaveURL(/\/register/);
  });

  test('signup requires all fields', async ({ page }) => {
    await page.goto('/register');
    
    // Try to submit empty form
    await page.getByRole('button', { name: /Sign up|Create account/i }).click();
    
    // Should stay on signup page
    await expect(page).toHaveURL(/\/register/);
  });
});
