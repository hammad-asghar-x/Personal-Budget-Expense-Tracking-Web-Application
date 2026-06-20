import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e/specs',
  timeout: 60_000,
  expect: { timeout: 5000 },
  fullyParallel: true,
  retries: 1,
  workers: 4,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: 'http://localhost:8000',
    headless: true,
    viewport: { width: 1280, height: 720 },
    actionTimeout: 10000,
    trace: 'on-first-retry',
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
  ],
});
