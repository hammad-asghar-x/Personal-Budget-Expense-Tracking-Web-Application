import { test, expect } from '@playwright/test';
import { DashboardPage } from '../pages/DashboardPage';
import { login } from '../helpers/auth';

test('navigate to dashboard and verify totals', async ({ page }) => {
  const dashboard = new DashboardPage(page);

  await login(page, 'hammad.asghar@student.pk', 'password');
  await dashboard.goto();

  await expect(dashboard.getTotalSpentLocator()).resolves.toBeTruthy();
});
