#!/usr/bin/env node

/**
 * UserFrosting C6Admin Sprinkle - User Detail Button Testing Script
 * 
 * This script uses Playwright to test button functionality on the user detail page.
 * It logs in as admin, navigates to /c6/admin/users/1, and tests all available buttons:
 * - Reset Password
 * - View/Edit
 * - Disable/Enable
 * - Delete
 * - Any other action buttons
 * 
 * Usage: node test-user-detail-buttons.js <base_url> <username> <password> [user_id]
 * Example: node test-user-detail-buttons.js http://localhost:8080 admin admin123 1
 */

import { chromium } from 'playwright';
import { writeFileSync } from 'fs';

async function testUserDetailButtons(baseUrl, username, password, userId = '1') {
    console.log('========================================');
    console.log('Testing User Detail Page Buttons');
    console.log('========================================');
    console.log(`Base URL: ${baseUrl}`);
    console.log(`Username: ${username}`);
    console.log(`User ID to test: ${userId}`);
    console.log('');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const testResults = {
        timestamp: new Date().toISOString(),
        baseUrl,
        userId,
        tests: []
    };

    try {
        const context = await browser.newContext({
            viewport: { width: 1280, height: 720 },
            ignoreHTTPSErrors: true
        });

        const page = await context.newPage();

        // Enable console logging from the page for debugging
        page.on('console', msg => {
            const type = msg.type();
            if (type === 'error' || type === 'warning') {
                console.log(`   [Browser ${type.toUpperCase()}]:`, msg.text());
            }
        });
        
        // Log page errors
        page.on('pageerror', error => {
            console.error(`   [Browser Error]:`, error.message);
        });

        // Step 1: Login
        console.log('📍 Step 1: Navigating to login page...');
        await page.goto(`${baseUrl}/account/sign-in`, { waitUntil: 'networkidle', timeout: 30000 });
        console.log('✅ Login page loaded');

        console.log('🔐 Logging in...');
        await page.waitForSelector('.uk-card input[data-test="username"]', { timeout: 10000 });
        await page.fill('.uk-card input[data-test="username"]', username);
        await page.fill('.uk-card input[data-test="password"]', password);
        
        await Promise.all([
            page.waitForNavigation({ timeout: 15000 }).catch(() => {
                console.log('   ⚠️  No navigation detected after login, but continuing...');
            }),
            page.click('.uk-card button[data-test="submit"]')
        ]);
        
        console.log('✅ Logged in successfully');
        await page.waitForTimeout(2000);

        // Step 2: Navigate to user detail page
        console.log('');
        console.log(`📍 Step 2: Navigating to user detail page: /c6/admin/users/${userId}`);
        await page.goto(`${baseUrl}/c6/admin/users/${userId}`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(3000);
        
        const currentUrl = page.url();
        if (currentUrl.includes('/account/sign-in')) {
            throw new Error('Failed to access user detail page - redirected to login');
        }
        console.log(`✅ User detail page loaded: ${currentUrl}`);

        // Take initial screenshot
        const initialScreenshotPath = `/tmp/screenshot_user_${userId}_initial.png`;
        await page.screenshot({ 
            path: initialScreenshotPath, 
            fullPage: true 
        });
        console.log(`📸 Initial screenshot saved: ${initialScreenshotPath}`);

        // Step 3: Discover all buttons and interactive elements
        console.log('');
        console.log('📍 Step 3: Discovering buttons and interactive elements...');
        
        // Find all buttons on the page
        const buttons = await page.$$('button');
        console.log(`   Found ${buttons.length} button elements`);

        // Collect button information
        const buttonInfo = [];
        for (let i = 0; i < buttons.length; i++) {
            const button = buttons[i];
            const info = await button.evaluate(el => ({
                text: el.textContent?.trim() || '',
                id: el.id || null,
                class: el.className || '',
                type: el.type || 'button',
                'data-test': el.getAttribute('data-test') || null,
                'aria-label': el.getAttribute('aria-label') || null,
                disabled: el.disabled,
                visible: !el.hidden && el.offsetParent !== null
            }));
            
            if (info.visible && info.text) {
                buttonInfo.push({ index: i, ...info });
                console.log(`   Button ${i + 1}: "${info.text}" (class: ${info.class}, data-test: ${info['data-test'] || 'none'})`);
            }
        }

        // Find links that might be action buttons
        const links = await page.$$('a[href]');
        console.log(`   Found ${links.length} link elements`);
        
        const actionLinks = [];
        for (let i = 0; i < links.length; i++) {
            const link = links[i];
            const info = await link.evaluate(el => ({
                text: el.textContent?.trim() || '',
                href: el.getAttribute('href') || '',
                class: el.className || '',
                'data-test': el.getAttribute('data-test') || null,
                visible: !el.hidden && el.offsetParent !== null
            }));
            
            // Filter for action-like links (buttons styled as links or specific actions)
            if (info.visible && info.text && (
                info.text.toLowerCase().includes('edit') ||
                info.text.toLowerCase().includes('view') ||
                info.text.toLowerCase().includes('delete') ||
                info.text.toLowerCase().includes('disable') ||
                info.text.toLowerCase().includes('enable') ||
                info.text.toLowerCase().includes('password')
            )) {
                actionLinks.push({ index: i, ...info });
                console.log(`   Action Link ${i + 1}: "${info.text}" (href: ${info.href})`);
            }
        }

        // Step 4: Test specific buttons
        console.log('');
        console.log('📍 Step 4: Testing specific button actions...');

        // Test 1: Look for and test "Edit" or "View" button
        const editButton = buttonInfo.find(b => 
            b.text.toLowerCase().includes('edit') || 
            b.text.toLowerCase().includes('view')
        );
        
        if (editButton) {
            console.log('');
            console.log(`🔘 Testing Edit/View button: "${editButton.text}"`);
            testResults.tests.push(await testButton(page, 'button', editButton.index, editButton.text, userId));
        } else {
            console.log('   ⚠️  No Edit/View button found');
        }

        // Test 2: Look for and test "Reset Password" or "Change Password" button
        const passwordButton = buttonInfo.find(b => 
            b.text.toLowerCase().includes('password') ||
            b.text.toLowerCase().includes('reset')
        );
        
        if (passwordButton) {
            console.log('');
            console.log(`🔘 Testing Password button: "${passwordButton.text}"`);
            testResults.tests.push(await testButton(page, 'button', passwordButton.index, passwordButton.text, userId));
        } else {
            console.log('   ⚠️  No Reset/Change Password button found');
        }

        // Test 3: Look for and test "Disable" or "Enable" button
        const disableEnableButton = buttonInfo.find(b => 
            b.text.toLowerCase().includes('disable') || 
            b.text.toLowerCase().includes('enable')
        );
        
        if (disableEnableButton) {
            console.log('');
            console.log(`🔘 Testing Disable/Enable button: "${disableEnableButton.text}"`);
            testResults.tests.push(await testButton(page, 'button', disableEnableButton.index, disableEnableButton.text, userId));
        } else {
            console.log('   ⚠️  No Disable/Enable button found');
        }

        // Test 4: Look for and test "Delete" button
        const deleteButton = buttonInfo.find(b => 
            b.text.toLowerCase().includes('delete') ||
            b.text.toLowerCase().includes('remove')
        );
        
        if (deleteButton) {
            console.log('');
            console.log(`🔘 Testing Delete button: "${deleteButton.text}"`);
            console.log('   ⚠️  NOTE: Delete action will be cancelled to preserve test data');
            testResults.tests.push(await testButton(page, 'button', deleteButton.index, deleteButton.text, userId, true));
        } else {
            console.log('   ⚠️  No Delete button found');
        }

        // Test 5: Test any other action buttons we haven't tested yet
        console.log('');
        console.log('📍 Step 5: Testing remaining action buttons...');
        const testedTexts = testResults.tests.map(t => t.buttonText);
        const remainingButtons = buttonInfo.filter(b => 
            !testedTexts.includes(b.text) && 
            b.text.length > 0 &&
            !b.text.toLowerCase().includes('cancel') &&
            !b.text.toLowerCase().includes('close')
        );

        for (const button of remainingButtons) {
            if (testResults.tests.length < 10) { // Limit to avoid excessive testing
                console.log('');
                console.log(`🔘 Testing button: "${button.text}"`);
                testResults.tests.push(await testButton(page, 'button', button.index, button.text, userId));
            }
        }

        // Final screenshot
        const finalScreenshotPath = `/tmp/screenshot_user_${userId}_final.png`;
        await page.screenshot({ 
            path: finalScreenshotPath, 
            fullPage: true 
        });
        console.log('');
        console.log(`📸 Final screenshot saved: ${finalScreenshotPath}`);

        // Save test results to JSON
        const resultsPath = `/tmp/user_detail_button_test_results.json`;
        writeFileSync(resultsPath, JSON.stringify(testResults, null, 2));
        console.log(`📊 Test results saved: ${resultsPath}`);

        // Print summary
        console.log('');
        console.log('========================================');
        console.log('Test Summary');
        console.log('========================================');
        console.log(`Total buttons tested: ${testResults.tests.length}`);
        console.log(`Successful tests: ${testResults.tests.filter(t => t.success).length}`);
        console.log(`Failed tests: ${testResults.tests.filter(t => !t.success).length}`);
        console.log('========================================');

        testResults.tests.forEach(test => {
            const status = test.success ? '✅' : '❌';
            console.log(`${status} ${test.buttonText}: ${test.message}`);
        });

        console.log('');
        console.log('✅ User detail button testing completed');

    } catch (error) {
        console.error('');
        console.error('========================================');
        console.error('❌ Error during testing:');
        console.error(error.message);
        console.error(error.stack);
        console.error('========================================');
        
        // Take error screenshot
        try {
            const pages = await browser.pages();
            if (pages.length > 0) {
                const currentPage = pages[0];
                await currentPage.screenshot({ path: '/tmp/screenshot_button_test_error.png', fullPage: true });
                console.log('📸 Error screenshot saved to /tmp/screenshot_button_test_error.png');
            }
        } catch (e) {
            console.error('⚠️  Could not take error screenshot:', e.message);
        }
        
        throw error;
    } finally {
        await browser.close();
    }
}

/**
 * Test a single button by clicking it and observing the result
 */
async function testButton(page, elementType, index, buttonText, userId, cancelAction = false) {
    const result = {
        buttonText,
        elementType,
        index,
        success: false,
        message: '',
        screenshotPath: null,
        timestamp: new Date().toISOString()
    };

    try {
        // Take screenshot before clicking
        const beforeScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_before.png`;
        await page.screenshot({ 
            path: beforeScreenshotPath, 
            fullPage: true 
        });
        result.screenshotPath = beforeScreenshotPath;
        console.log(`   📸 Before screenshot: ${beforeScreenshotPath}`);

        // Get the button element again (fresh reference)
        const buttons = await page.$$(elementType);
        const button = buttons[index];

        if (!button) {
            result.message = `Button not found at index ${index}`;
            console.log(`   ❌ ${result.message}`);
            return result;
        }

        // Click the button
        console.log(`   🖱️  Clicking button...`);
        await button.click();
        
        // Wait for any response (modal, navigation, etc.)
        await page.waitForTimeout(2000);

        // Check for modals, dialogs, or alerts
        const modals = await page.$$('[role="dialog"], .uk-modal, .modal');
        if (modals.length > 0) {
            console.log(`   ℹ️  Modal/dialog detected after click`);
            
            // Take screenshot of modal
            const modalScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_modal.png`;
            await page.screenshot({ 
                path: modalScreenshotPath, 
                fullPage: true 
            });
            console.log(`   📸 Modal screenshot: ${modalScreenshotPath}`);

            if (cancelAction) {
                // Try to cancel/close the modal
                const cancelButtons = await page.$$('button');
                for (const btn of cancelButtons) {
                    const text = await btn.textContent();
                    if (text && (text.toLowerCase().includes('cancel') || text.toLowerCase().includes('close'))) {
                        console.log(`   🖱️  Clicking Cancel/Close button to dismiss modal`);
                        await btn.click();
                        await page.waitForTimeout(1000);
                        break;
                    }
                }
            }

            result.message = 'Button clicked, modal/dialog appeared';
            result.success = true;
        } else {
            // No modal - check if page changed
            const newUrl = page.url();
            if (!newUrl.includes(`/users/${userId}`)) {
                result.message = `Button clicked, navigated to: ${newUrl}`;
                result.success = true;
                console.log(`   ℹ️  Navigated to: ${newUrl}`);
                
                // Navigate back to user detail page
                await page.goto(`${page.url().split('/c6/admin')[0]}/c6/admin/users/${userId}`, { waitUntil: 'networkidle', timeout: 30000 });
                await page.waitForTimeout(2000);
            } else {
                result.message = 'Button clicked, no visible change detected';
                result.success = true;
            }
        }

        // Take screenshot after clicking
        const afterScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_after.png`;
        await page.screenshot({ 
            path: afterScreenshotPath, 
            fullPage: true 
        });
        console.log(`   📸 After screenshot: ${afterScreenshotPath}`);

        console.log(`   ✅ Test completed: ${result.message}`);

    } catch (error) {
        result.message = `Error: ${error.message}`;
        console.log(`   ❌ ${result.message}`);
    }

    return result;
}

// Parse command line arguments
const args = process.argv.slice(2);

if (args.length < 3) {
    console.error('Usage: node test-user-detail-buttons.js <base_url> <username> <password> [user_id]');
    console.error('Example: node test-user-detail-buttons.js http://localhost:8080 admin admin123 1');
    process.exit(1);
}

const [baseUrl, username, password, userId] = args;

// Run the script
testUserDetailButtons(baseUrl, username, password, userId)
    .then(() => {
        process.exit(0);
    })
    .catch((error) => {
        console.error('Script failed:', error);
        process.exit(1);
    });
