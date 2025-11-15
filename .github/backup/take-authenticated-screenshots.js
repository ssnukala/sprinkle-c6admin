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
        
        // Give Vue.js time to render the login form after page load
        console.log('⏳ Waiting for login form to render...');
        await page.waitForTimeout(3000);
        
        // Wait for the login form to be visible (UserFrosting 6 uses data-test attributes)
        // Try without .uk-card first for better compatibility
        console.log('🔍 Looking for username input field...');
        try {

            await page.waitForSelector('.uk-card input[data-test="username"]', { timeout: 10000 });
            
            //await page.waitForSelector('input[data-test="username"]', { timeout: 30000, state: 'visible' });
            console.log('✅ Username input field found');
        } catch (error) {
            console.error('❌ Could not find username input field');
            console.log('📝 Available inputs on page:');
            const inputs = await page.$$('input');
            for (let i = 0; i < inputs.length; i++) {
                const attrs = await inputs[i].evaluate(el => ({
                    type: el.type,
                    name: el.name,
                    id: el.id,
                    'data-test': el.getAttribute('data-test'),
                    class: el.className
                }));
                console.log(`  Input ${i + 1}:`, JSON.stringify(attrs));
            }
            throw error;
        }

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
        let currentUrl = page.url();
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
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
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
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const userDetailScreenshotPath = '/tmp/screenshot_c6admin_user_detail.png';
        await page.screenshot({ 
            path: userDetailScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${userDetailScreenshotPath}`);

        // Step 6: Take screenshot of groups list page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/groups');
        await page.goto(`${baseUrl}/c6/admin/groups`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const groupsScreenshotPath = '/tmp/screenshot_c6admin_groups_list.png';
        await page.screenshot({ 
            path: groupsScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${groupsScreenshotPath}`);

        // Step 7: Take screenshot of single group page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/groups/1');
        await page.goto(`${baseUrl}/c6/admin/groups/1`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const groupDetailScreenshotPath = '/tmp/screenshot_c6admin_group_detail.png';
        await page.screenshot({ 
            path: groupDetailScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${groupDetailScreenshotPath}`);

        // Step 8: Take screenshot of roles list page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/roles');
        await page.goto(`${baseUrl}/c6/admin/roles`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const rolesScreenshotPath = '/tmp/screenshot_c6admin_roles_list.png';
        await page.screenshot({ 
            path: rolesScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${rolesScreenshotPath}`);

        // Step 9: Take screenshot of single role page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/roles/1');
        await page.goto(`${baseUrl}/c6/admin/roles/1`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const roleDetailScreenshotPath = '/tmp/screenshot_c6admin_role_detail.png';
        await page.screenshot({ 
            path: roleDetailScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${roleDetailScreenshotPath}`);

        // Step 10: Take screenshot of permissions list page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/permissions');
        await page.goto(`${baseUrl}/c6/admin/permissions`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const permissionsScreenshotPath = '/tmp/screenshot_c6admin_permissions_list.png';
        await page.screenshot({ 
            path: permissionsScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${permissionsScreenshotPath}`);

        // Step 11: Take screenshot of single permission page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/permissions/1');
        await page.goto(`${baseUrl}/c6/admin/permissions/1`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const permissionDetailScreenshotPath = '/tmp/screenshot_c6admin_permission_detail.png';
        await page.screenshot({ 
            path: permissionDetailScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${permissionDetailScreenshotPath}`);

        // Step 12: Take screenshot of activities page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/activities');
        await page.goto(`${baseUrl}/c6/admin/activities`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const activitiesScreenshotPath = '/tmp/screenshot_c6admin_activities.png';
        await page.screenshot({ 
            path: activitiesScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${activitiesScreenshotPath}`);

        // Step 13: Take screenshot of config info page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/config/info');
        await page.goto(`${baseUrl}/c6/admin/config/info`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const configInfoScreenshotPath = '/tmp/screenshot_c6admin_config_info.png';
        await page.screenshot({ 
            path: configInfoScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${configInfoScreenshotPath}`);

        // Step 14: Take screenshot of config cache page
        console.log('');
        console.log('📸 Taking screenshot: /c6/admin/config/cache');
        await page.goto(`${baseUrl}/c6/admin/config/cache`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);
        
        currentUrl = page.url();
        if (!currentUrl.includes('/account/sign-in')) {
            console.log(`✅ Page loaded: ${currentUrl}`);
        }
        
        const configCacheScreenshotPath = '/tmp/screenshot_c6admin_config_cache.png';
        await page.screenshot({ 
            path: configCacheScreenshotPath, 
            fullPage: true 
        });
        console.log(`✅ Screenshot saved: ${configCacheScreenshotPath}`);

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
            const pages = await browser.pages();
            if (pages.length > 0) {
                const currentPage = pages[0];
                await currentPage.screenshot({ path: '/tmp/screenshot_error.png', fullPage: true });
                console.log('📸 Error screenshot saved to /tmp/screenshot_error.png');
                
                // Log current URL and page title for debugging
                const currentUrl = currentPage.url();
                const title = await currentPage.title();
                console.log(`📍 Error occurred on page: ${currentUrl}`);
                console.log(`📄 Page title: ${title}`);
            }
        } catch (e) {
            console.error('⚠️  Could not take error screenshot:', e.message);
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
