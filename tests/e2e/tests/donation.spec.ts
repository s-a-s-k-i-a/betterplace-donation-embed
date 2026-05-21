/**
 * E2E: betterplace-donation-embed.
 *
 * Verifies:
 *   1. The shortcode renders an iframe with the expected attributes on the frontend.
 *   2. The block is registered with WordPress (queryable via REST).
 *   3. The frontend never references the upstream load_donation_iframe.js.
 *
 * Editor UI interaction (insert block via inserter) is intentionally not tested
 * here — that flow is highly coupled to specific Gutenberg/WP versions and
 * tends to be flaky in CI. Block registration via REST + frontend rendering
 * give us the meaningful coverage at much lower flakiness cost.
 */
import { test, expect, request as pwRequest } from '@playwright/test';
import { execSync } from 'node:child_process';
import * as path from 'node:path';

const PROJECT_ID = 4667;
const REPO_ROOT = path.resolve( __dirname, '../../..' );

function wpCli( cmd: string ): string {
	return execSync( `npx wp-env run cli wp ${ cmd }`, {
		encoding: 'utf-8',
		stdio: [ 'ignore', 'pipe', 'pipe' ],
		cwd: REPO_ROOT,
	} ).trim();
}

test.describe( 'Frontend rendering via shortcode', () => {
	let postId: number;
	let postUrl: string;

	test.beforeAll( () => {
		const shortcode = `[betterplace_donation project_id="${ PROJECT_ID }" color="ff0000" default_amount="25"]`;
		// Use --post_content=- via stdin would be ideal but execSync makes it awkward;
		// wp-env's quoting is sensitive, so single-quote the whole arg and escape inner double-quotes.
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
				// ignore cleanup errors
			}
		}
	} );

	test( 'page contains exactly one betterplace iframe with sanitized attributes', async ( {
		page,
	} ) => {
		await page.goto( postUrl, { waitUntil: 'domcontentloaded' } );

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
		await page.goto( postUrl, { waitUntil: 'domcontentloaded' } );

		const fallback = page.locator(
			`a[href*="/donate/platform/projects/${ PROJECT_ID }"]`
		);
		await expect( fallback ).toHaveCount( 1 );
		await expect( fallback ).toHaveAttribute( 'target', '_blank' );
		await expect( fallback ).toHaveAttribute( 'rel', /noopener/ );
	} );

	test( 'no load_donation_iframe.js anywhere in the page HTML', async ( {
		baseURL,
	} ) => {
		const ctx = await pwRequest.newContext( { baseURL } );
		const res = await ctx.get( postUrl );
		const body = await res.text();

		expect( body ).not.toContain( 'load_donation_iframe.js' );
		expect( body ).not.toContain( 'betterplace-assets.betterplace.org' );
	} );
} );

test.describe( 'Block registration (REST)', () => {
	test( 'block "betterplace-embed/donation" is registered', async ( {
		baseURL,
	} ) => {
		// Fetch the public block-types REST endpoint (anonymous).
		const ctx = await pwRequest.newContext( { baseURL } );
		const res = await ctx.get( '/wp-json/wp/v2/block-types' );
		expect( res.status() ).toBe( 200 );

		const blocks = ( await res.json() ) as Array< { name: string } >;
		const names = blocks.map( ( b ) => b.name );

		expect( names ).toContain( 'betterplace-embed/donation' );
	} );
} );
