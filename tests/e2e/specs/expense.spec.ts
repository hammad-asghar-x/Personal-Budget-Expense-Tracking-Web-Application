import { test, expect } from '@playwright/test';
import { ExpensePage } from '../pages/ExpensePage';
import { login } from '../helpers/auth';

test('create expense via UI', async ({ page }) => {
  const expensePage = new ExpensePage(page);

  await login(page, 'hammad.asghar@student.pk', 'password');
  await expensePage.gotoCreate();

  const title = `Lunch ${Date.now()}`;
  await expensePage.createExpense({ title, amount: '9.99', date: new Date().toISOString().slice(0,10) });

  await expect(page.locator('text=Expense created successfully')).toBeVisible({ timeout: 10000 });
  await expect(page.locator(`text=${title}`)).toBeVisible({ timeout: 10000 });
});
