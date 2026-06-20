import { test, expect } from '@playwright/test';
import { CategoryPage } from '../pages/CategoryPage';
import { login } from '../helpers/auth';

test('create category flow', async ({ page }) => {
  const categoryPage = new CategoryPage(page);

  await login(page, 'hammad.asghar@student.pk', 'password');
  await categoryPage.goto();

  const name = `TestCat-${Date.now()}`;
  await categoryPage.addCategory(name);

  await expect(categoryPage.categoryRow(name)).toBeVisible();
});
