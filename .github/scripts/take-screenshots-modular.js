#!/usr/bin/env node

/**
 * UserFrosting C6Admin Sprinkle Integration Test - Modular Screenshot Script
 * 
 * This script uses Playwright to take screenshots based on a JSON configuration.
 * It reads the paths configuration and takes screenshots for all frontend paths
 * that have the "screenshot" flag set to true.
 * 
 * Usage: node take-screenshots-modular.js <config_file> [base_url] [username] [password]
 * Example: node take-screenshots-modular.js .github/config/integration-test-paths.json
 * Example: node take-screenshots-modular.js .github/config/integration-test-paths.json http://localhost:8080 admin admin123
 */

import { chromium } from 'playwright';
import { readFileSync } from 'fs';

/**
 * Wait for Vue.js app to be fully mounted and ready
 */
async function waitForVueApp(page, timeout = 10000) {
    console.log('   ⏳ Waiting for Vue.js app to mount...');
    try {
        await page.waitForFunction(() => {
            // Check if Vue app is mounted
            const app = document.querySelector('#app, [data-v-app], .v-application');
            if (!app) return false;
            
            // Check if Vue instance exists (Vue 3)
            if (window.__VUE_DEVTOOLS_GLOBAL_HOOK__) return true;
            
            // Check for Vue 2
            if (window.Vue) return true;
            
            // Check if main content is rendered (not just loading state)
            const hasContent = app.querySelector('.uk-card, .card, main, [role="main"]');
            return hasContent !== null;
        }, { timeout });
        console.log('   ✅ Vue.js app mounted');
        return true;
    } catch (error) {
        console.warn(`   ⚠️  Vue.js app mount timeout: ${error.message}`);
        return false;
    }
}

/**
 * Check for JavaScript errors and blockers
 */
async function checkJavaScriptStatus(page) {
    console.log('   🔍 Checking JavaScript status...');
    
    const jsStatus = await page.evaluate(() => {
        const errors = [];
        const warnings = [];
        
        // Check if JavaScript is enabled
        const jsEnabled = typeof window !== 'undefined';
        
        // Check for common blocking issues
        const cspMeta = document.querySelector('meta[http-equiv="Content-Security-Policy"]');
        if (cspMeta) {
            warnings.push(`CSP meta tag found: ${cspMeta.content?.substring(0, 100)}`);
        }
        
        // Check for Vue
        const hasVue = !!(window.Vue || window.__VUE_DEVTOOLS_GLOBAL_HOOK__);
        
        // Check for Vue Router
        const hasVueRouter = !!(window.VueRouter || (window.__VUE_DEVTOOLS_GLOBAL_HOOK__?.apps?.[0]?.appContext?.config));
        
        // Check for app element
        const appElement = document.querySelector('#app, [data-v-app]');
        const appMounted = appElement && appElement.children.length > 0;
        
        return {
            jsEnabled,
            hasVue,
            hasVueRouter,
            appMounted,
            errors,
            warnings,
            appElementFound: !!appElement,
            appChildrenCount: appElement?.children.length || 0
        };
    });
    
    console.log(`      JavaScript enabled: ${jsStatus.jsEnabled ? '✅' : '❌'}`);
    console.log(`      Vue.js detected: ${jsStatus.hasVue ? '✅' : '❌'}`);
    console.log(`      Vue Router detected: ${jsStatus.hasVueRouter ? '✅' : '❌'}`);
    console.log(`      App element found: ${jsStatus.appElementFound ? '✅' : '❌'}`);
    console.log(`      App mounted: ${jsStatus.appMounted ? '✅' : '❌'} (${jsStatus.appChildrenCount} children)`);
    
    if (jsStatus.warnings.length > 0) {
        console.warn(`      Warnings: ${jsStatus.warnings.join(', ')}`);
    }
    
    if (jsStatus.errors.length > 0) {
        console.error(`      Errors: ${jsStatus.errors.join(', ')}`);
    }
    
    return jsStatus;
}

async function takeScreenshotsFromConfig(configFile, baseUrlOverride, usernameOverride, passwordOverride) {
    console.log('========================================');
    console.log('Taking Screenshots from Configuration');
    console.log('========================================');
    console.log(`Config file: ${configFile}`);
    console.log('');

    // Load configuration
    let config;
    try {
        const configContent = readFileSync(configFile, 'utf8');
        config = JSON.parse(configContent);
    } catch (error) {
        console.error(`❌ Failed to load configuration: ${error.message}`);
        process.exit(1);
    }

    // Get credentials from config or command line
    const baseUrl = baseUrlOverride || config.config?.base_url || 'http://localhost:8080';
    const username = usernameOverride || config.config?.auth?.username || 'admin';
    const password = passwordOverride || config.config?.auth?.password || 'admin123';

    console.log(`Base URL: ${baseUrl}`);
    console.log(`Username: ${username}`);
    console.log('');

    // Collect screenshots to take
    const screenshots = [];
    
    if (config.paths?.authenticated?.frontend) {
        for (const [name, pathConfig] of Object.entries(config.paths.authenticated.frontend)) {
            if (pathConfig.screenshot && !pathConfig.skip) {
                screenshots.push({
                    name,
                    path: pathConfig.path,
                    description: pathConfig.description || name,
                    screenshot_name: pathConfig.screenshot_name || name
                });
            }
        }
    }

    if (screenshots.length === 0) {
        console.log('ℹ️  No screenshots configured');
        return;
    }

    console.log(`Found ${screenshots.length} screenshots to capture\n`);

    // Launch browser
    const browser = await chromium.launch({
        headless: true,
        args: [
            '--no-sandbox', 
            '--disable-setuid-sandbox',
            '--disable-web-security',  // Allow cross-origin requests
            '--disable-features=IsolateOrigins,site-per-process'  // Disable site isolation
        ]
    });

    try {
        const context = await browser.newContext({
            viewport: { width: 1280, height: 720 },
            ignoreHTTPSErrors: true,
            javaScriptEnabled: true,  // Explicitly enable JavaScript
            bypassCSP: true  // Bypass Content Security Policy that might block scripts
        });

        const page = await context.newPage();

        // Step 1: Navigate to login page and authenticate
        console.log('📍 Navigating to login page...');
        console.log(`   URL: ${baseUrl}/account/sign-in`);
        
        // Enable console logging from the page - LOG ERRORS ONLY
        page.on('console', msg => {
            const type = msg.type();
            // Only log browser errors (not log/debug/warning messages)
            if (type === 'error') {
                console.log(`   [Browser ${type.toUpperCase()}]:`, msg.text());
            }
            // Commenting out non-error logs to reduce noise during integration testing
            // Uncomment these when debugging is needed:
            // if (type === 'log' || type === 'debug' || type === 'warning') {
            //     console.log(`   [Browser ${type.toUpperCase()}]:`, msg.text());
            // }
        });
        
        // Log page errors
        page.on('pageerror', error => {
            console.error(`   [Browser Error]:`, error.message);
        });
        
        // Log failed requests
        page.on('requestfailed', request => {
            console.error(`   [Request Failed]: ${request.url()} - ${request.failure()?.errorText || 'Unknown error'}`);
        });
        
        await page.goto(`${baseUrl}/account/sign-in`, { waitUntil: 'networkidle', timeout: 30000 });
        console.log('✅ Login page loaded');
        console.log(`   Current URL: ${page.url()}`);

        console.log('🔐 Logging in...');
        
        // Wait for the login form to be visible
        console.log('   Waiting for username input field...');
        await page.waitForSelector('.uk-card input[data-test="username"]', { timeout: 10000 });
        console.log('   ✅ Username field found');
        
        // Fill in credentials
        console.log(`   Filling username: ${username}`);
        await page.fill('.uk-card input[data-test="username"]', username);
        console.log('   Filling password: ********');
        await page.fill('.uk-card input[data-test="password"]', password);
        
        // Click the login button and wait for navigation
        console.log('   Clicking login button...');
        await Promise.all([
            page.waitForNavigation({ timeout: 15000 }).catch(() => {
                console.log('   ⚠️  No navigation detected after login, but continuing...');
            }),
            page.click('.uk-card button[data-test="submit"]')
        ]);
        
        console.log('✅ Logged in successfully');
        console.log(`   Current URL after login: ${page.url()}`);
        
        // Give session a moment to stabilize
        await page.waitForTimeout(2000);
        
        // Check JavaScript and Vue status after login
        await checkJavaScriptStatus(page);
        await waitForVueApp(page);

        // Step 2: Take screenshots from configuration
        let successCount = 0;
        let failCount = 0;

        for (const screenshot of screenshots) {
            console.log('');
            console.log(`📸 Taking screenshot: ${screenshot.name}`);
            console.log(`   Path: ${screenshot.path}`);
            console.log(`   Description: ${screenshot.description}`);

            try {
                console.log(`   Navigating to: ${baseUrl}${screenshot.path}`);
                await page.goto(`${baseUrl}${screenshot.path}`, { waitUntil: 'networkidle', timeout: 30000 });
                
                // Wait for page content to load
                console.log('   Waiting for page to stabilize...');
                await page.waitForTimeout(2000);
                
                // Wait for Vue app to be ready
                await waitForVueApp(page);
                
                // Check JavaScript status on this page
                const jsStatus = await checkJavaScriptStatus(page);
                
                // Check if we're still on login page (would indicate auth failure)
                const currentUrl = page.url();
                console.log(`   Current URL: ${currentUrl}`);
                
                if (currentUrl.includes('/account/sign-in')) {
                    console.warn(`   ⚠️  Warning: Still on login page - authentication may have failed`);
                    console.warn(`   Expected path: ${screenshot.path}`);
                    console.warn(`   Actual URL: ${currentUrl}`);
                    
                    // Take a debug screenshot
                    const debugPath = `/tmp/screenshot_${screenshot.screenshot_name}_debug.png`;
                    await page.screenshot({ 
                        path: debugPath, 
                        fullPage: true 
                    });
                    console.warn(`   📸 Debug screenshot saved: ${debugPath}`);
                    failCount++;
                } else {
                    console.log(`   ✅ Page loaded successfully`);
                    
                    // Check for UFAlert components (error alerts should fail the test)
                    console.log('   🔍 Checking for UFAlert components...');
                    const alerts = await page.evaluate(() => {
                        const alertElements = document.querySelectorAll('[data-alert], .uf-alert, .alert');
                        return Array.from(alertElements).map(el => ({
                            text: el.textContent?.trim() || '',
                            className: el.className || '',
                            isError: el.classList.contains('alert-danger') || 
                                    el.classList.contains('uk-alert-danger') ||
                                    el.getAttribute('data-alert-type') === 'error' ||
                                    el.getAttribute('data-alert-type') === 'danger',
                            isWarning: el.classList.contains('alert-warning') || 
                                      el.classList.contains('uk-alert-warning') ||
                                      el.getAttribute('data-alert-type') === 'warning'
                        }));
                    });
                    
                    if (alerts.length > 0) {
                        console.log(`   ℹ️  Found ${alerts.length} alert(s):`);
                        alerts.forEach((alert, idx) => {
                            const type = alert.isError ? 'ERROR' : (alert.isWarning ? 'WARNING' : 'INFO');
                            console.log(`      ${idx + 1}. [${type}] ${alert.text.substring(0, 100)}`);
                        });
                        
                        // Fail the test if there are error alerts
                        const errorAlerts = alerts.filter(a => a.isError);
                        if (errorAlerts.length > 0) {
                            console.error(`   ❌ FAIL: Found ${errorAlerts.length} error alert(s) on page!`);
                            errorAlerts.forEach((alert, idx) => {
                                console.error(`      ${idx + 1}. ${alert.text}`);
                            });
                            
                            // Take error screenshot
                            const errorPath = `/tmp/screenshot_${screenshot.screenshot_name}_error.png`;
                            await page.screenshot({ 
                                path: errorPath, 
                                fullPage: true 
                            });
                            console.error(`   📸 Error screenshot saved: ${errorPath}`);
                            failCount++;
                            continue; // Skip to next screenshot
                        }
                    } else {
                        console.log('   ✅ No alerts found');
                    }
                    
                    // Check for UFModal components and wait for content to render
                    console.log('   🔍 Checking for UFModal components...');
                    
                    // First check if modal is opening (backdrop visible)
                    const modalBackdrop = await page.evaluate(() => {
                        const backdrop = document.querySelector('.uk-modal-page, .modal-backdrop, [class*="modal-backdrop"]');
                        return backdrop !== null;
                    });
                    
                    if (modalBackdrop) {
                        console.log('   ⏳ Modal backdrop detected - waiting for modal content to render...');
                        
                        // Wait for modal dialog content to actually render
                        try {
                            await page.waitForSelector(
                                '[role="dialog"] .uk-modal-dialog, .uk-modal.uk-open .uk-modal-dialog, .modal.show .modal-dialog',
                                { state: 'visible', timeout: 5000 }
                            );
                            console.log('   ✅ Modal dialog content appeared');
                            
                            // Additional wait for Vue to render modal content
                            await page.waitForTimeout(2000);
                        } catch (e) {
                            console.warn('   ⚠️  Modal dialog content did not appear within 5 seconds');
                            console.warn('   ⚠️  This may explain "navbar in middle" symptom - backdrop visible but no dialog');
                        }
                    }
                    
                    const modals = await page.evaluate(() => {
                        // Check for various modal selectors
                        const modalSelectors = [
                            '[role="dialog"]',
                            '.uk-modal.uk-open',
                            '.modal.show',
                            '.uf-modal'
                        ];
                        
                        const foundModals = [];
                        modalSelectors.forEach(selector => {
                            const elements = document.querySelectorAll(selector);
                            elements.forEach(el => {
                                const computedStyle = getComputedStyle(el);
                                const isVisible = el.offsetParent !== null || computedStyle.display !== 'none';
                                
                                // Check if modal has actual content
                                const dialog = el.querySelector('.uk-modal-dialog, .modal-dialog, .modal-content');
                                const hasContent = dialog && dialog.children.length > 0;
                                
                                if (isVisible) {
                                    foundModals.push({
                                        selector,
                                        visible: true,
                                        hasContent,
                                        title: el.querySelector('[data-modal-title], .modal-title, .uk-modal-title')?.textContent?.trim() || '',
                                        dialogFound: !!dialog,
                                        contentChildCount: dialog?.children.length || 0
                                    });
                                }
                            });
                        });
                        
                        return foundModals;
                    });
                    
                    if (modals.length > 0) {
                        console.log(`   ℹ️  Found ${modals.length} open modal(s):`);
                        modals.forEach((modal, idx) => {
                            const status = modal.hasContent ? '✅ HAS CONTENT' : '⚠️  NO CONTENT';
                            console.log(`      ${idx + 1}. ${modal.selector} - "${modal.title}" [${status}]`);
                            console.log(`         Dialog found: ${modal.dialogFound ? 'Yes' : 'No'}, Children: ${modal.contentChildCount}`);
                        });
                        
                        // Check if any modals have content
                        const modalsWithContent = modals.filter(m => m.hasContent);
                        if (modalsWithContent.length === 0) {
                            console.warn('   ⚠️  Modal detected but NO CONTENT rendered!');
                            console.warn('   ⚠️  This explains "navbar in middle" - backdrop visible, dialog empty');
                        }
                        
                        // Take modal screenshot with suffix
                        const modalPath = `/tmp/screenshot_${screenshot.screenshot_name}_with_modal.png`;
                        await page.screenshot({ 
                            path: modalPath, 
                            fullPage: true 
                        });
                        console.log(`   📸 Modal screenshot saved: ${modalPath}`);
                    } else {
                        console.log('   ✅ No modals detected');
                    }
                    
                    // Take regular screenshot
                    const screenshotPath = `/tmp/screenshot_${screenshot.screenshot_name}.png`;
                    await page.screenshot({ 
                        path: screenshotPath, 
                        fullPage: true 
                    });
                    console.log(`   ✅ Screenshot saved: ${screenshotPath}`);
                    successCount++;
                }
            } catch (error) {
                console.error(`   ❌ Failed: ${error.message}`);
                console.error(`   Stack: ${error.stack}`);
                
                // Try to get page content for debugging
                try {
                    const pageContent = await page.content();
                    console.error(`   Page title: ${await page.title()}`);
                    console.error(`   Page URL: ${page.url()}`);
                } catch (e) {
                    console.error(`   Could not retrieve page details: ${e.message}`);
                }
                
                failCount++;
            }
        }

        console.log('');
        console.log('========================================');
        console.log('Screenshot Summary');
        console.log('========================================');
        console.log(`Total: ${screenshots.length}`);
        console.log(`Success: ${successCount}`);
        console.log(`Failed: ${failCount}`);
        console.log('========================================');

        if (failCount > 0) {
            console.log('⚠️  Some screenshots failed');
        } else {
            console.log('✅ All screenshots taken successfully');
        }

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

if (args.length < 1) {
    console.error('Usage: node take-screenshots-modular.js <config_file> [base_url] [username] [password]');
    console.error('Example: node take-screenshots-modular.js integration-test-paths.json');
    console.error('Example: node take-screenshots-modular.js integration-test-paths.json http://localhost:8080 admin admin123');
    process.exit(1);
}

const [configFile, baseUrl, username, password] = args;

// Run the script
takeScreenshotsFromConfig(configFile, baseUrl, username, password)
    .then(() => {
        process.exit(0);
    })
    .catch((error) => {
        console.error('Script failed:', error);
        process.exit(1);
    });
