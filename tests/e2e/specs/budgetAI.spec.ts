import { test, expect } from '@playwright/test';
import { BudgetAIPage } from '../pages/BudgetAIPage';
import { login } from '../helpers/auth';

test('request budget recommendation', async ({ page }) => {
  const ai = new BudgetAIPage(page);

  await login(page, 'hammad.asghar@student.pk', 'password');
  await ai.gotoBudgetAI();
  await ai.selectBudgetPeriod('5', '2026');
  await ai.requestRecommendation();
  await ai.waitForRecommendationOutcome();
});
