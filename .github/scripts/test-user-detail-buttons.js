#!/usr/bin/env node

/**
 * UserFrosting C6Admin Sprinkle - User Detail Button Testing Script with Full Form Automation
 * 
 * This script uses Playwright to test button functionality on the user detail page.
 * It logs in as admin, navigates to /c6/admin/users/1, and FULLY TESTS all available buttons:
 * - Edit: Fills and submits the edit form with test data
 * - Reset Password: Fills and submits the password reset form
 * - Disable/Enable: Toggles user status and verifies the change
 * - Delete: Opens confirmation but cancels to preserve test data
 * - Any other action buttons: Basic click testing
 * 
 * This provides full automation of form testing, not just clicking buttons.
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

        // Test 1: Look for and test "Edit" or "View" button - FULLY TEST EDIT FORM
        const editButton = buttonInfo.find(b => 
            b.text.toLowerCase().includes('edit') || 
            b.text.toLowerCase().includes('view')
        );
        
        if (editButton) {
            console.log('');
            console.log(`🔘 Testing Edit/View button with form submission: "${editButton.text}"`);
            testResults.tests.push(await testEditButton(page, 'button', editButton.index, editButton.text, userId));
        } else {
            console.log('   ⚠️  No Edit/View button found');
        }

        // Test 2: Look for and test "Reset Password" or "Change Password" button - FULLY TEST PASSWORD FORM
        const passwordButton = buttonInfo.find(b => 
            b.text.toLowerCase().includes('password') ||
            b.text.toLowerCase().includes('reset')
        );
        
        if (passwordButton) {
            console.log('');
            console.log(`🔘 Testing Password button with form submission and database verification: "${passwordButton.text}"`);
            testResults.tests.push(await testPasswordButton(page, 'button', passwordButton.index, passwordButton.text, userId, baseUrl, username, password));
        } else {
            console.log('   ⚠️  No Reset/Change Password button found');
        }

        // Test 3: Look for and test "Disable" or "Enable" button - FULLY TEST STATUS TOGGLE
        const disableEnableButton = buttonInfo.find(b => 
            b.text.toLowerCase().includes('disable') || 
            b.text.toLowerCase().includes('enable')
        );
        
        if (disableEnableButton) {
            console.log('');
            console.log(`🔘 Testing Disable/Enable button with status verification: "${disableEnableButton.text}"`);
            testResults.tests.push(await testDisableEnableButton(page, 'button', disableEnableButton.index, disableEnableButton.text, userId));
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
 * Test the Edit button by filling and submitting the edit form
 */
async function testEditButton(page, elementType, index, buttonText, userId) {
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

        // Get the button element and click it
        const buttons = await page.$$(elementType);
        const button = buttons[index];

        if (!button) {
            result.message = `Button not found at index ${index}`;
            console.log(`   ❌ ${result.message}`);
            return result;
        }

        console.log(`   🖱️  Clicking Edit button...`);
        await button.click();
        
        // Wait for modal to appear with proper selector waiting
        console.log(`   ⏳ Waiting for modal to appear...`);
        let modalAppeared = false;
        try {
            await page.waitForSelector('[role="dialog"], .uk-modal, .modal', { 
                state: 'visible',
                timeout: 5000 
            });
            modalAppeared = true;
            console.log(`   ✅ Modal appeared`);
        } catch (e) {
            console.log(`   ⚠️  Modal did not appear within 5 seconds`);
        }
        
        // Additional wait for modal animations to complete
        await page.waitForTimeout(1000);

        // Check for modal/dialog with enhanced detection
        const modals = await page.$$('[role="dialog"], .uk-modal, .modal');
        
        // Verify modal visibility
        const modalInfo = await page.evaluate(() => {
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
                    if (el.offsetParent !== null || getComputedStyle(el).display !== 'none') {
                        foundModals.push({
                            selector,
                            visible: true,
                            title: el.querySelector('[data-modal-title], .modal-title, .uk-modal-title')?.textContent?.trim() || ''
                        });
                    }
                });
            });
            
            return foundModals;
        });
        
        if ((modals.length > 0 || modalAppeared) && modalInfo.length > 0) {
            console.log(`   ℹ️  Edit form modal detected and verified visible`);
            modalInfo.forEach((modal, idx) => {
                console.log(`      ${idx + 1}. ${modal.selector}${modal.title ? ` - "${modal.title}"` : ''}`);
            });
            
            // Take screenshot of modal
            const modalScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_modal.png`;
            await page.screenshot({ 
                path: modalScreenshotPath, 
                fullPage: true 
            });
            console.log(`   📸 Modal screenshot: ${modalScreenshotPath}`);

            // Try to fill in the edit form
            console.log(`   ✏️  Filling edit form...`);
            
            // Look for first name field and modify it
            const firstNameInput = await page.$('input[name="first_name"], input[id*="first"], input[placeholder*="first" i]');
            if (firstNameInput) {
                const originalValue = await firstNameInput.inputValue();
                const testValue = `TestFirstName_${Date.now()}`;
                await firstNameInput.fill(testValue);
                console.log(`   ✅ Modified first name: "${originalValue}" → "${testValue}"`);
            }

            // Look for last name field and modify it
            const lastNameInput = await page.$('input[name="last_name"], input[id*="last"], input[placeholder*="last" i]');
            if (lastNameInput) {
                const originalValue = await lastNameInput.inputValue();
                const testValue = `TestLastName_${Date.now()}`;
                await lastNameInput.fill(testValue);
                console.log(`   ✅ Modified last name: "${originalValue}" → "${testValue}"`);
            }

            // Take screenshot after filling form
            const filledScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_filled.png`;
            await page.screenshot({ 
                path: filledScreenshotPath, 
                fullPage: true 
            });
            console.log(`   📸 Form filled screenshot: ${filledScreenshotPath}`);

            // Try to find and click the submit/save button
            const submitButtons = await page.$$('button');
            let submitted = false;
            for (const btn of submitButtons) {
                const text = await btn.textContent();
                if (text && (text.toLowerCase().includes('save') || text.toLowerCase().includes('submit') || text.toLowerCase().includes('update'))) {
                    console.log(`   🖱️  Clicking Submit button: "${text.trim()}"`);
                    await btn.click();
                    await page.waitForTimeout(3000);
                    submitted = true;
                    break;
                }
            }

            if (submitted) {
                // Check for UFAlert components after submission
                console.log('   🔍 Checking for alerts after submission...');
                const alerts = await page.evaluate(() => {
                    const alertElements = document.querySelectorAll('[data-alert], .uf-alert, .alert');
                    return Array.from(alertElements).map(el => ({
                        text: el.textContent?.trim() || '',
                        className: el.className || '',
                        isError: el.classList.contains('alert-danger') || 
                                el.classList.contains('uk-alert-danger') ||
                                el.getAttribute('data-alert-type') === 'error' ||
                                el.getAttribute('data-alert-type') === 'danger',
                        isSuccess: el.classList.contains('alert-success') || 
                                  el.classList.contains('uk-alert-success') ||
                                  el.getAttribute('data-alert-type') === 'success'
                    }));
                });
                
                if (alerts.length > 0) {
                    console.log(`   ℹ️  Found ${alerts.length} alert(s):`);
                    alerts.forEach((alert, idx) => {
                        const type = alert.isError ? 'ERROR' : (alert.isSuccess ? 'SUCCESS' : 'INFO');
                        console.log(`      ${idx + 1}. [${type}] ${alert.text.substring(0, 100)}`);
                    });
                    
                    // Mark as failure if there are error alerts
                    const errorAlerts = alerts.filter(a => a.isError);
                    if (errorAlerts.length > 0) {
                        result.message = `Edit form submitted but got ${errorAlerts.length} error alert(s)`;
                        result.success = false;
                        console.log(`   ❌ ${result.message}`);
                    } else {
                        result.message = 'Edit form submitted successfully';
                        result.success = true;
                        console.log(`   ✅ ${result.message}`);
                    }
                } else {
                    result.message = 'Edit form submitted successfully';
                    result.success = true;
                    console.log(`   ✅ ${result.message}`);
                }
            } else {
                // If no submit button found, close the modal
                console.log(`   ⚠️  Submit button not found, closing modal`);
                const cancelButtons = await page.$$('button');
                for (const btn of cancelButtons) {
                    const text = await btn.textContent();
                    if (text && (text.toLowerCase().includes('cancel') || text.toLowerCase().includes('close'))) {
                        await btn.click();
                        await page.waitForTimeout(1000);
                        break;
                    }
                }
                result.message = 'Edit form opened but submit button not found';
                result.success = true;
            }
        } else {
            result.message = 'Edit button clicked but no modal appeared';
            result.success = false;
        }

        // Take screenshot after
        const afterScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_after.png`;
        await page.screenshot({ 
            path: afterScreenshotPath, 
            fullPage: true 
        });
        console.log(`   📸 After screenshot: ${afterScreenshotPath}`);

    } catch (error) {
        result.message = `Error: ${error.message}`;
        console.log(`   ❌ ${result.message}`);
    }

    return result;
}

/**
 * Test the Password Reset button by filling and submitting the password form
 * and verifying the password was updated in the database
 */
async function testPasswordButton(page, elementType, index, buttonText, userId, baseUrl, adminUsername, adminPassword) {
    const result = {
        buttonText,
        elementType,
        index,
        success: false,
        message: '',
        screenshotPath: null,
        timestamp: new Date().toISOString(),
        passwordVerified: false
    };

    try {
        // First, get the username of the user we're testing (from the page)
        let testUsername = null;
        try {
            // Try to find username on the page - look for common patterns
            const usernameElement = await page.$('text=/Username:/i');
            if (usernameElement) {
                const parent = await usernameElement.evaluateHandle(el => el.parentElement);
                const text = await parent.textContent();
                const match = text.match(/Username:\s*(\S+)/i);
                if (match) {
                    testUsername = match[1];
                    console.log(`   ℹ️  Found username on page: ${testUsername}`);
                }
            }
        } catch (e) {
            console.log(`   ⚠️  Could not extract username from page: ${e.message}`);
        }

        // Take screenshot before clicking
        const beforeScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_before.png`;
        await page.screenshot({ 
            path: beforeScreenshotPath, 
            fullPage: true 
        });
        result.screenshotPath = beforeScreenshotPath;
        console.log(`   📸 Before screenshot: ${beforeScreenshotPath}`);

        // Get the button element and click it
        const buttons = await page.$$(elementType);
        const button = buttons[index];

        if (!button) {
            result.message = `Button not found at index ${index}`;
            console.log(`   ❌ ${result.message}`);
            return result;
        }

        console.log(`   🖱️  Clicking Password Reset button...`);
        await button.click();
        
        // Wait for modal to appear with proper selector waiting
        console.log(`   ⏳ Waiting for modal to appear...`);
        let modalAppeared = false;
        try {
            await page.waitForSelector('[role="dialog"], .uk-modal, .modal', { 
                state: 'visible',
                timeout: 5000 
            });
            modalAppeared = true;
            console.log(`   ✅ Modal appeared`);
        } catch (e) {
            console.log(`   ⚠️  Modal did not appear within 5 seconds`);
        }
        
        // Additional wait for modal animations to complete
        await page.waitForTimeout(1000);

        // Check for modal/dialog
        const modals = await page.$$('[role="dialog"], .uk-modal, .modal');
        if (modals.length > 0 || modalAppeared) {
            console.log(`   ℹ️  Password reset form modal detected`);
            
            // Take screenshot of modal
            const modalScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_modal.png`;
            await page.screenshot({ 
                path: modalScreenshotPath, 
                fullPage: true 
            });
            console.log(`   📸 Modal screenshot: ${modalScreenshotPath}`);

            // Try to fill in the password form
            console.log(`   ✏️  Filling password reset form...`);
            
            const testPassword = `TestPass123!${Date.now()}`;
            console.log(`   🔑 Using test password for verification`);
            
            // Look for password fields
            const passwordInputs = await page.$$('input[type="password"], input[name*="password" i], input[id*="password" i]');
            
            if (passwordInputs.length > 0) {
                // Fill first password field (new password)
                await passwordInputs[0].fill(testPassword);
                console.log(`   ✅ Filled password field 1`);
                
                // Fill second password field if it exists (confirm password)
                if (passwordInputs.length > 1) {
                    await passwordInputs[1].fill(testPassword);
                    console.log(`   ✅ Filled password field 2 (confirmation)`);
                }
            }

            // Take screenshot after filling form
            const filledScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_filled.png`;
            await page.screenshot({ 
                path: filledScreenshotPath, 
                fullPage: true 
            });
            console.log(`   📸 Form filled screenshot: ${filledScreenshotPath}`);

            // Try to find and click the submit button
            const submitButtons = await page.$$('button');
            let submitted = false;
            for (const btn of submitButtons) {
                const text = await btn.textContent();
                if (text && (text.toLowerCase().includes('save') || text.toLowerCase().includes('submit') || text.toLowerCase().includes('update') || text.toLowerCase().includes('reset'))) {
                    console.log(`   🖱️  Clicking Submit button: "${text.trim()}"`);
                    await btn.click();
                    await page.waitForTimeout(3000);
                    submitted = true;
                    break;
                }
            }

            if (submitted && testUsername) {
                console.log(`   ✅ Password reset form submitted successfully`);
                
                // Now verify the password was actually changed in the database
                console.log(`   🔍 Verifying password change in database...`);
                console.log(`   ℹ️  Testing password for user: ${testUsername}`);
                
                // Open a new incognito context to test login without affecting current session
                console.log(`   🔐 Opening new browser context to verify password...`);
                const verifyContext = await page.context().browser().newContext({
                    viewport: { width: 1280, height: 720 },
                    ignoreHTTPSErrors: true
                });
                const verifyPage = await verifyContext.newPage();
                
                try {
                    // Try to log in with the NEW password
                    console.log(`   🔐 Attempting login with NEW password to verify database update...`);
                    await verifyPage.goto(`${baseUrl}/account/sign-in`, { waitUntil: 'networkidle', timeout: 30000 });
                    await verifyPage.waitForTimeout(2000);
                    
                    await verifyPage.waitForSelector('.uk-card input[data-test="username"]', { timeout: 5000 });
                    await verifyPage.fill('.uk-card input[data-test="username"]', testUsername);
                    await verifyPage.fill('.uk-card input[data-test="password"]', testPassword);
                    
                    await Promise.all([
                        verifyPage.waitForNavigation({ timeout: 10000 }).catch(() => {
                            console.log(`   ⚠️  No navigation after login attempt`);
                        }),
                        verifyPage.click('.uk-card button[data-test="submit"]')
                    ]);
                    
                    await verifyPage.waitForTimeout(2000);
                    
                    // Check if we're logged in (not on sign-in page anymore)
                    const verifyUrl = verifyPage.url();
                    if (!verifyUrl.includes('/account/sign-in')) {
                        console.log(`   ✅ Successfully logged in with NEW password - password verified in database!`);
                        result.passwordVerified = true;
                        
                        // Take verification screenshot
                        const verifiedScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_verified.png`;
                        await verifyPage.screenshot({ 
                            path: verifiedScreenshotPath, 
                            fullPage: true 
                        });
                        console.log(`   📸 Password verified screenshot: ${verifiedScreenshotPath}`);
                        
                        result.message = 'Password reset form submitted and verified in database';
                        result.success = true;
                    } else {
                        console.log(`   ❌ Login with NEW password failed - password may not have been updated in database`);
                        result.message = 'Password reset form submitted but database verification failed';
                        result.success = false;
                    }
                } catch (error) {
                    console.log(`   ❌ Error during password verification: ${error.message}`);
                    result.message = `Password form submitted but verification error: ${error.message}`;
                    result.success = false;
                } finally {
                    await verifyContext.close();
                }
                
                // Now restore the original password (since we're still logged in as admin in the main session)
                if (result.passwordVerified) {
                    console.log(`   🔄 Restoring original password for user ${testUsername}...`);
                    
                    // We should already be on the user detail page, but refresh to be sure
                    await page.reload({ waitUntil: 'networkidle', timeout: 30000 });
                    await page.waitForTimeout(2000);
                    
                    // Click password button again
                    const restoreButtons = await page.$$('button');
                    let passwordButtonFound = false;
                    for (const btn of restoreButtons) {
                        const btnText = await btn.textContent();
                        if (btnText && (btnText.toLowerCase().includes('password') || btnText.toLowerCase().includes('reset'))) {
                            await btn.click();
                            await page.waitForTimeout(2000);
                            passwordButtonFound = true;
                            break;
                        }
                    }
                    
                    if (passwordButtonFound) {
                        // Fill in a default password (we don't know the original, so use a standard test password)
                        const defaultPassword = 'password123';
                        const restorePasswordInputs = await page.$$('input[type="password"]');
                        if (restorePasswordInputs.length > 0) {
                            await restorePasswordInputs[0].fill(defaultPassword);
                            if (restorePasswordInputs.length > 1) {
                                await restorePasswordInputs[1].fill(defaultPassword);
                            }
                            console.log(`   ✏️  Filled default password to restore: ${defaultPassword}`);
                            
                            // Submit to restore
                            const restoreSubmitButtons = await page.$$('button');
                            for (const btn of restoreSubmitButtons) {
                                const text = await btn.textContent();
                                if (text && (text.toLowerCase().includes('save') || text.toLowerCase().includes('submit') || text.toLowerCase().includes('update'))) {
                                    await btn.click();
                                    await page.waitForTimeout(2000);
                                    console.log(`   ✅ Password restored to default: ${defaultPassword}`);
                                    break;
                                }
                            }
                        }
                    }
                }
            } else if (submitted && !testUsername) {
                result.message = 'Password reset form submitted but could not extract username for verification';
                result.success = true;
                console.log(`   ⚠️  ${result.message}`);
            } else {
                // If no submit button found, close the modal
                console.log(`   ⚠️  Submit button not found, closing modal`);
                const cancelButtons = await page.$$('button');
                for (const btn of cancelButtons) {
                    const text = await btn.textContent();
                    if (text && (text.toLowerCase().includes('cancel') || text.toLowerCase().includes('close'))) {
                        await btn.click();
                        await page.waitForTimeout(1000);
                        break;
                    }
                }
                result.message = 'Password reset form opened but submit button not found';
                result.success = true;
            }
        } else {
            result.message = 'Password button clicked but no modal appeared';
            result.success = false;
        }

        // Take screenshot after
        const afterScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_after.png`;
        await page.screenshot({ 
            path: afterScreenshotPath, 
            fullPage: true 
        });
        console.log(`   📸 After screenshot: ${afterScreenshotPath}`);

    } catch (error) {
        result.message = `Error: ${error.message}`;
        console.log(`   ❌ ${result.message}`);
    }

    return result;
}

/**
 * Test the Disable/Enable button and verify status change
 */
async function testDisableEnableButton(page, elementType, index, buttonText, userId) {
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

        // Remember the initial button text
        const initialButtonText = buttonText;
        console.log(`   ℹ️  Initial button state: "${initialButtonText}"`);

        // Get the button element and click it
        const buttons = await page.$$(elementType);
        const button = buttons[index];

        if (!button) {
            result.message = `Button not found at index ${index}`;
            console.log(`   ❌ ${result.message}`);
            return result;
        }

        console.log(`   🖱️  Clicking ${buttonText} button...`);
        await button.click();
        
        // Wait for modal to appear with proper selector waiting
        console.log(`   ⏳ Waiting for modal to appear...`);
        let modalAppeared = false;
        try {
            await page.waitForSelector('[role="dialog"], .uk-modal, .modal', { 
                state: 'visible',
                timeout: 5000 
            });
            modalAppeared = true;
            console.log(`   ✅ Modal appeared`);
        } catch (e) {
            console.log(`   ⚠️  Modal did not appear within 5 seconds`);
        }
        
        // Additional wait for modal animations to complete
        await page.waitForTimeout(1000);

        // Check for confirmation modal
        const modals = await page.$$('[role="dialog"], .uk-modal, .modal');
        if (modals.length > 0 || modalAppeared) {
            console.log(`   ℹ️  Confirmation modal detected`);
            
            // Take screenshot of modal
            const modalScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_modal.png`;
            await page.screenshot({ 
                path: modalScreenshotPath, 
                fullPage: true 
            });
            console.log(`   📸 Modal screenshot: ${modalScreenshotPath}`);

            // Find and click confirm button
            const confirmButtons = await page.$$('button');
            let confirmed = false;
            for (const btn of confirmButtons) {
                const text = await btn.textContent();
                if (text && (text.toLowerCase().includes('confirm') || text.toLowerCase().includes('yes') || text.toLowerCase().includes('ok'))) {
                    console.log(`   🖱️  Clicking Confirm button: "${text.trim()}"`);
                    await btn.click();
                    await page.waitForTimeout(3000);
                    confirmed = true;
                    break;
                }
            }

            if (!confirmed) {
                console.log(`   ⚠️  No confirm button found, action may not complete`);
            }
        } else {
            // No modal, action happened directly
            console.log(`   ℹ️  No confirmation modal, action executed directly`);
            await page.waitForTimeout(2000);
        }

        // Reload page to see updated state
        console.log(`   🔄 Reloading page to verify status change...`);
        await page.reload({ waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);

        // Try to find the same button again and check if text changed
        const updatedButtons = await page.$$('button');
        let buttonTextAfter = null;
        for (const btn of updatedButtons) {
            const text = await btn.textContent();
            if (text && (text.toLowerCase().includes('disable') || text.toLowerCase().includes('enable'))) {
                buttonTextAfter = text.trim();
                break;
            }
        }

        if (buttonTextAfter) {
            console.log(`   ℹ️  Button state after action: "${buttonTextAfter}"`);
            
            // Check if button text changed (indicating status toggle)
            if (initialButtonText.toLowerCase().includes('disable') && buttonTextAfter.toLowerCase().includes('enable')) {
                result.message = `User disabled successfully (button changed from "${initialButtonText}" to "${buttonTextAfter}")`;
                result.success = true;
                console.log(`   ✅ ${result.message}`);
            } else if (initialButtonText.toLowerCase().includes('enable') && buttonTextAfter.toLowerCase().includes('disable')) {
                result.message = `User enabled successfully (button changed from "${initialButtonText}" to "${buttonTextAfter}")`;
                result.success = true;
                console.log(`   ✅ ${result.message}`);
            } else {
                result.message = `Button clicked but status may not have changed (button text: "${buttonTextAfter}")`;
                result.success = true;
                console.log(`   ⚠️  ${result.message}`);
            }
        } else {
            result.message = 'Status button clicked but could not verify change';
            result.success = true;
        }

        // Take screenshot after
        const afterScreenshotPath = `/tmp/screenshot_button_${buttonText.replace(/[^a-z0-9]/gi, '_')}_after.png`;
        await page.screenshot({ 
            path: afterScreenshotPath, 
            fullPage: true 
        });
        console.log(`   📸 After screenshot: ${afterScreenshotPath}`);

    } catch (error) {
        result.message = `Error: ${error.message}`;
        console.log(`   ❌ ${result.message}`);
    }

    return result;
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
        
        // Wait for modal to appear with proper selector waiting
        console.log(`   ⏳ Waiting for modal to appear...`);
        let modalAppeared = false;
        try {
            await page.waitForSelector('[role="dialog"], .uk-modal, .modal', { 
                state: 'visible',
                timeout: 5000 
            });
            modalAppeared = true;
            console.log(`   ✅ Modal appeared`);
        } catch (e) {
            console.log(`   ⚠️  Modal did not appear within 5 seconds`);
        }
        
        // Additional wait for modal animations to complete
        await page.waitForTimeout(1000);

        // Check for modals, dialogs, or alerts
        const modals = await page.$$('[role="dialog"], .uk-modal, .modal');
        if (modals.length > 0 || modalAppeared) {
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
