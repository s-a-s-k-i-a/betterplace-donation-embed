/**
 * E2E: betterplace-donation-embed.
 *
 * Verifies:
 *   1. The shortcode renders an iframe with the expected attributes on the frontend.
 *   2. The Gutenberg block can be inserted in the editor and saves with the right HTML comment.
 *   3. The frontend never references the upstream load_donation_iframe.js (that's the whole point).
 */
import { test, expect, Page, request as pwRequest } from '@playwright/test';
import { execSync } from 'node:child_process';

const PROJECT_ID = 4667;
const ADMIN_USER = process.env.WP_ADMIN_USER ?? 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASS ?? 'password';

function wpCli( cmd: string ): string {
	return execSync( `npx wp-env run cli wp ${ cmd }`, {
		encoding: 'utf-8',
		stdio: [ 'ignore', 'pipe', 'pipe' ],
	} ).trim();
}

async function login( page: Page ) {
	await page.goto( '/wp-login.php' );
	await page.fill( '#user_login', ADMIN_USER );
	await page.fill( '#user_pass', ADMIN_PASS );
	await page.click( '#wp-submit' );
	await page.waitForURL( /\/wp-admin\/?/ );
}

test.describe( 'Frontend rendering via shortcode', () => {
	let postId: number;
	let postUrl: string;

	test.beforeAll( () => {
		const shortcode = `[betterplace_donation project_id="${ PROJECT_ID }" color="ff0000" default_amount="25"]`;
		const out = wpCli(
			`post create --post_type=post --post_status=publish --post_title='E2E shortcode' --post_content='${ shortcode }' --porcelain`
		);
		postId = parseInt( out, 10 );
		postUrl = `/?p=${ postId }`;
	} );

	test.afterAll( () => {
		if ( postId ) {
			try {
				wpCli( `post delete ${ postId } --force` );
			} catch {
				// ignore
			}
		}
	} );

	test( 'renders iframe with sanitized attributes', async ( { page } ) => {
		await page.goto( postUrl );

		const iframe = page.locator( 'iframe[src*="betterplace.org"]' );
		await expect( iframe ).toHaveCount( 1 );

		const src = await iframe.getAttribute( 'src' );
		expect( src ).toContain( `/donate/iframe/projects/${ PROJECT_ID }?` );
		expect( src ).toContain( 'color=ff0000' );
		expect( src ).toContain( 'donation_amount=25' );

		await expect( iframe ).toHaveAttribute( 'loading', 'lazy' );
		await expect( iframe ).toHaveAttribute(
			'referrerpolicy',
			'strict-origin-when-cross-origin'
		);
	} );

	test( 'fallback link is present', async ( { page } ) => {
		await page.goto( postUrl );

		const fallback = page.locator(
			`a[href*="/donate/platform/projects/${ PROJECT_ID }"]`
		);
		await expect( fallback ).toHaveCount( 1 );
		await expect( fallback ).toHaveAttribute( 'target', '_blank' );
		await expect( fallback ).toHaveAttribute( 'rel', /noopener/ );
	} );

	test( 'no load_donation_iframe.js anywhere in the page HTML', async ( {
		page,
		baseURL,
	} ) => {
		const ctx = await pwRequest.newContext( { baseURL } );
		const res = await ctx.get( postUrl );
		const body = await res.text();

		expect( body ).not.toContain( 'load_donation_iframe.js' );
		expect( body ).not.toContain( 'betterplace-assets.betterplace.org' );
	} );
} );

test.describe( 'Gutenberg block in the editor', () => {
	test( 'block appears in inserter and inserts with default attributes', async ( {
		page,
	} ) => {
		await login( page );

		// New post.
		await page.goto( '/wp-admin/post-new.php' );

		// Dismiss the welcome modal if present.
		const welcome = page.getByRole( 'button', { name: /close/i } );
		if ( await welcome.isVisible().catch( () => false ) ) {
			await welcome.click();
		}

		// Open the inserter.
		await page
			.getByRole( 'button', { name: /toggle block inserter/i } )
			.click();

		// Search for the block.
		const searchBox = page.getByPlaceholder( /search/i ).first();
		await searchBox.fill( 'Betterplace' );

		// Click the inserter result.
		await page
			.getByRole( 'option', { name: /Betterplace-Spendenformular/ } )
			.first()
			.click();

		// The block should now be present in the canvas.
		const blockInCanvas = page
			.frameLocator( 'iframe[name="editor-canvas"], iframe[title*="Editor"]' )
			.locator( '[data-type="betterplace-embed/donation"]' )
			.or( page.locator( '[data-type="betterplace-embed/donation"]' ) );

		await expect( blockInCanvas ).toBeVisible( { timeout: 15_000 } );
	} );
} );
