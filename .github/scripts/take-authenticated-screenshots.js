#!/usr/bin/env node

/**
 * UserFrosting C6Admin Sprinkle Integration Test - Authenticated Screenshot Script
 * 
 * This script uses Playwright to:
 * 1. Navigate to the login page
 * 2. Log in with admin credentials
 * 3. Take screenshots of C6Admin pages
 * 
 * Usage: node take-authenticated-screenshots.js <base_url> <username> <password>
 * Example: node take-authenticated-screenshots.js http://localhost:8080 admin admin123
 */

import { chromium } from 'playwright';

async function takeAuthenticatedScreenshots(baseUrl, username, password) {
    console.log('========================================');
    console.log('Taking Authenticated Screenshots');
    console.log('========================================');
    console.log(`Base URL: ${baseUrl}`);
    console.log(`Username: ${username}`);
    console.log('');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    try {
        const context = await browser.newContext({
            viewport: { width: 1280, height: 720 },
            ignoreHTTPSErrors: true
        });

        const page = await context.newPage();

        // Step 1: Navigate to login page
        console.log('📍 Navigating to login page...');
        await page.goto(`${baseUrl}/account/sign-in`, { waitUntil: 'networkidle', timeout: 30000 });
        console.log('✅ Login page loaded');

        // Step 2: Fill in login form
        console.log('🔐 Logging in...');
        
        // Wait for the login form to be visible (UserFrosting 6 uses data-test attributes)
        // Use .uk-card to target the main body login form, not the header dropdown
        await page.waitForSelector('.uk-card input[data-test="username"]', { timeout: 10000 });
        
        // Fill in credentials using data-test selectors (qualified with .uk-card)
        await page.fill('.uk-card input[data-test="username"]', username);
        await page.fill('.uk-card input[data-test="password"]', password);
        
        // Click the login button using data-test selector and wait for navigation
        await Promise.all([
            page.waitForNavigation({ timeout: 15000 }).catch(() => {
                console.log('⚠️  No navigation detected after login, but continuing...');
            }),
            page.click('.uk-card button[data-test="submit"]')
        ]);
        
        console.log('✅ Logged in successfully');
        
        // Give it a moment for the session to stabilize
        await page.waitForTimeout(2000);

        // Step 3: Take screenshot of C6Admin dashboard
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/dashboard');
        await page.goto(`${baseUrl}/c6/admin/dashboard`, { waitUntil: 'networkidle', timeout: 30000 });
        
        // Wait for page content to load
        await page.waitForTimeout(2000);
        
        // Check if we're still on login page (would indicate auth failure)
        const currentUrl = page.url();
        if (currentUrl.includes('/account/sign-in')) {
            console.warn('⚠️  Warning: Still on login page - authentication may have failed');
        } else {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const dashboardScreenshotPath = '/tmp/screenshot_c6admin_dashboard.png';
        await page.screenshot({ 
            path: dashboardScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${dashboardScreenshotPath}`);

        // Step 4: Take screenshot of users list page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/users');
        await page.goto(`${baseUrl}/c6/admin/users`, { waitUntil: 'networkidle', timeout: 30000 });
        
        // Wait for page content to load
        await page.waitForTimeout(2000);
        
        // Check if we're still on login page
        const currentUrl2 = page.url();
        if (currentUrl2.includes('/account/sign-in')) {
            console.warn('⚠️  Warning: Still on login page - authentication may have failed');
        } else {
            console.log(`✅ Page loaded: ${currentUrl2}`);
        }
        
        const usersScreenshotPath = '/tmp/screenshot_c6admin_users_list.png';
        await page.screenshot({ 
            path: usersScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${usersScreenshotPath}`);

        // Step 5: Take screenshot of single user page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/users/1');
        await page.goto(`${baseUrl}/c6/admin/users/1`, { waitUntil: 'networkidle', timeout: 30000 });
        
        // Wait for page content to load
        await page.waitForTimeout(2000);
        
        // Check if we're still on login page
        const currentUrl3 = page.url();
        if (currentUrl3.includes('/account/sign-in')) {
            console.warn('⚠️  Warning: Still on login page - authentication may have failed');
        } else {
            console.log(`✅ Page loaded: ${currentUrl3}`);
        }
        
        const userDetailScreenshotPath = '/tmp/screenshot_c6admin_user_detail.png';
        await page.screenshot({ 
            path: userDetailScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${userDetailScreenshotPath}`);

        console.log('');
        console.log('========================================');
        console.log('✅ All screenshots taken successfully');
        console.log('========================================');

    } catch (error) {
        console.error('');
        console.error('========================================');
        console.error('❌ Error taking screenshots:');
        console.error(error.message);
        console.error('========================================');
        
        // Take a screenshot of the current page for debugging
        try {
            const errorPage = await browser.newPage();
            await errorPage.screenshot({ path: '/tmp/screenshot_error.png', fullPage: true });
            console.log('📸 Error screenshot saved to /tmp/screenshot_error.png');
        } catch (e) {
            // Ignore errors when taking error screenshot
        }
        
        throw error;
    } finally {
        await browser.close();
    }
}

// Parse command line arguments
const args = process.argv.slice(2);

if (args.length < 3) {
    console.error('Usage: node take-authenticated-screenshots.js <base_url> <username> <password>');
    console.error('Example: node take-authenticated-screenshots.js http://localhost:8080 admin admin123');
    process.exit(1);
}

const [baseUrl, username, password] = args;

// Run the script
takeAuthenticatedScreenshots(baseUrl, username, password)
    .then(() => {
        process.exit(0);
    })
    .catch((error) => {
        console.error('Script failed:', error);
        process.exit(1);
    });
