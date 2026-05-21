import { defineConfig, devices } from '@playwright/test';

const WP_BASE_URL = process.env.WP_BASE_URL ?? 'http://localhost:8888';

export default defineConfig( {
	testDir: './tests',
	timeout: 90_000,
	fullyParallel: false,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: 1,
	reporter: [ [ 'list' ], [ 'html', { open: 'never' } ] ],
	use: {
		baseURL: WP_BASE_URL,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		video: 'retain-on-failure',
		ignoreHTTPSErrors: true,
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ] },
		},
	],
} );
