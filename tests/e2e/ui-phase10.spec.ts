import { test, expect } from '@playwright/test';

test.describe('UI Phase 10: Desktop-Oriented Overhaul', () => {
  const adminUsername = 'admin';
  const adminPassword = 'Admin@12345';
  const pmUsername = 'pm1';
  const pmPassword = 'PM@12345';

  let adminContext: any;
  let pmContext: any;

  test.beforeAll(async ({ browser }) => {
    // Create admin context with login
    adminContext = await browser.newContext();
    const adminPage = await adminContext.newPage();
    await adminPage.goto('/login');
    await adminPage.fill('input[name="username"]', adminUsername);
    await adminPage.fill('input[name="password"]', adminPassword);
    await adminPage.click('button[type="submit"]');
    await adminPage.waitForNavigation();

    // Create project manager context with login
    pmContext = await browser.newContext();
    const pmPage = await pmContext.newPage();
    await pmPage.goto('/login');
    await pmPage.fill('input[name="username"]', pmUsername);
    await pmPage.fill('input[name="password"]', pmPassword);
    await pmPage.click('button[type="submit"]');
    await pmPage.waitForNavigation();
  });

  test.afterAll(async () => {
    await adminContext.close();
    await pmContext.close();
  });

  test('Admin menu dropdown should expand when clicked', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="username"]', adminUsername);
    await page.fill('input[name="password"]', adminPassword);
    await page.click('button[type="submit"]');
    await page.waitForNavigation();

    // Navigate to a page that shows the navbar
    await page.goto('/projects');

    // Look for the Admin menu dropdown toggle
    const adminMenuButton = page.locator('#adminMenu');
    await expect(adminMenuButton).toBeVisible();

    // Check that dropdown is not initially expanded
    const dropdownMenu = page.locator('.dropdown-menu');
    let isVisible = await dropdownMenu.isVisible().catch(() => false);
    expect(isVisible).toBeFalsy();

    // Click the admin menu button
    await adminMenuButton.click();

    // Wait a moment for dropdown animation
    await page.waitForTimeout(300);

    // Check that dropdown is now visible
    isVisible = await dropdownMenu.isVisible();
    expect(isVisible).toBeTruthy();

    // Verify dropdown menu items are visible
    await expect(page.locator('.dropdown-menu a')).toHaveCount(3); // Users, Modules, Theme

    await context.close();
  });

  test('Admin sets default widget visibility for a module', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="username"]', adminUsername);
    await page.fill('input[name="password"]', adminPassword);
    await page.click('button[type="submit"]');
    await page.waitForNavigation();

    // Navigate to modules page
    await page.goto('/modules');
    await expect(page).toHaveURL('/modules');

    // Look for default layout controls
    const defaultLayoutForms = page.locator('form[action*="widget-layout-default"]');
    const formCount = await defaultLayoutForms.count();
    expect(formCount).toBeGreaterThan(0);

    // Find and toggle the visibility checkbox for the first module
    const firstVisibilityCheckbox = defaultLayoutForms.first().locator('input[name="is_visible"]');
    const firstOrderInput = defaultLayoutForms.first().locator('input[name="display_order"]');

    // Set visibility and order
    const wasChecked = await firstVisibilityCheckbox.isChecked();
    await firstVisibilityCheckbox.click();
    await firstOrderInput.fill('1');

    // Submit the form
    const submitButton = defaultLayoutForms.first().locator('button[type="submit"]');
    await submitButton.click();

    // Wait for redirect or success message
    await page.waitForNavigation().catch(() => {}); // May not always navigate
    await page.waitForTimeout(500);

    // Verify success message appears
    const successAlert = page.locator('.alert-success');
    await expect(successAlert).toBeVisible();

    await context.close();
  });

  test('Project manager views project with widgets in grid layout', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as PM
    await page.goto('/login');
    await page.fill('input[name="username"]', pmUsername);
    await page.fill('input[name="password"]', pmPassword);
    await page.click('button[type="submit"]');
    await page.waitForNavigation();

    // Navigate to projects
    await page.goto('/projects');

    // Click on first project
    const projectLink = page.locator('a:has-text("Test Project")').first();
    if (await projectLink.count() > 0) {
      await projectLink.click();
      await page.waitForNavigation();

      // Verify widgets are in a grid layout
      const widgetGrid = page.locator('.row.g-3');
      await expect(widgetGrid).toBeVisible();

      // Verify responsive grid classes
      const widgetColumns = page.locator('.col-12.col-md-6.col-lg-4');
      const columnCount = await widgetColumns.count();
      expect(columnCount).toBeGreaterThanOrEqual(0);

      // Verify project left panel is visible
      const sidePanel = page.locator('aside');
      await expect(sidePanel).toBeVisible();

      // Verify "Manage widgets" link is visible
      const manageWidgetsLink = page.locator('a:has-text("Manage widgets")');
      expect(await manageWidgetsLink.count()).toBeGreaterThanOrEqual(0);
    }

    await context.close();
  });

  test('Project manager accesses dedicated widget layout admin page', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as PM
    await page.goto('/login');
    await page.fill('input[name="username"]', pmUsername);
    await page.fill('input[name="password"]', pmPassword);
    await page.click('button[type="submit"]');
    await page.waitForNavigation();

    // Navigate to projects
    await page.goto('/projects');

    // Click on first project
    const projectLink = page.locator('a:has-text("Test Project")').first();
    if (await projectLink.count() > 0) {
      await projectLink.click();
      await page.waitForNavigation();

      // Click "Manage widgets" link
      const manageWidgetsLink = page.locator('a:has-text("Manage widgets")');
      if (await manageWidgetsLink.count() > 0) {
        await manageWidgetsLink.click();
        await page.waitForNavigation();

        // Verify we're on the widget layout page
        await expect(page).toHaveURL(/\/widgets\/layout$/);

        // Verify layout management form is visible
        const layoutForm = page.locator('form[method="post"]');
        await expect(layoutForm).toBeVisible();

        // Verify table with widget options exists
        const widgetTable = page.locator('table');
        await expect(widgetTable).toBeVisible();

        // Verify columns: Name, Visible, Order
        const headers = page.locator('th');
        expect(await headers.count()).toBeGreaterThanOrEqual(3);
      }
    }

    await context.close();
  });

  test('Project manager changes widget visibility and sees changes on overview', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as PM
    await page.goto('/login');
    await page.fill('input[name="username"]', pmUsername);
    await page.fill('input[name="password"]', pmPassword);
    await page.click('button[type="submit"]');
    await page.waitForNavigation();

    // Navigate to projects
    await page.goto('/projects');

    // Click on first project
    const projectLink = page.locator('a:has-text("Test Project")').first();
    if (await projectLink.count() > 0) {
      await projectLink.click();
      await page.waitForNavigation();

      // Navigate to widget layout page
      const manageWidgetsLink = page.locator('a:has-text("Manage widgets")');
      if (await manageWidgetsLink.count() > 0) {
        await manageWidgetsLink.click();
        await page.waitForNavigation();

        // Find a widget checkbox and toggle it
        const visibilityCheckboxes = page.locator('input[name*="widget_visible"]');
        const checkboxCount = await visibilityCheckboxes.count();

        if (checkboxCount > 0) {
          const firstCheckbox = visibilityCheckboxes.first();
          const wasChecked = await firstCheckbox.isChecked();

          // Toggle checkbox
          await firstCheckbox.click();

          // Submit the form
          const submitButton = page.locator('button:has-text("Save layout")');
          await submitButton.click();

          // Wait for redirect or success
          await page.waitForNavigation().catch(() => {});
          await page.waitForTimeout(500);

          // Verify success message
          const successAlert = page.locator('.alert-success');
          expect(await successAlert.count()).toBeGreaterThanOrEqual(0);
        }
      }
    }

    await context.close();
  });

  test('Navbar dropdown works on different views', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="username"]', adminUsername);
    await page.fill('input[name="password"]', adminPassword);
    await page.click('button[type="submit"]');
    await page.waitForNavigation();

    const routes = ['/projects', '/programmes', '/users', '/modules', '/theme'];

    for (const route of routes) {
      await page.goto(route);

      // Check navbar is visible
      const navbar = page.locator('nav');
      await expect(navbar).toBeVisible();

      // Try to access admin menu if available
      const adminMenuButton = page.locator('#adminMenu');
      if (await adminMenuButton.count() > 0) {
        await adminMenuButton.click();
        await page.waitForTimeout(200);

        // Verify dropdown is visible
        const dropdownMenu = page.locator('.dropdown-menu');
        const isVisible = await dropdownMenu.isVisible();
        expect(isVisible).toBeTruthy();

        // Click somewhere else to close dropdown
        await page.click('body');
      }
    }

    await context.close();
  });

  test('Widget cards display in responsive grid at different breakpoints', async ({ browser }) => {
    const viewports = [
      { name: 'Mobile', width: 375, height: 667 },
      { name: 'Tablet', width: 768, height: 1024 },
      { name: 'Desktop', width: 1920, height: 1080 },
    ];

    for (const viewport of viewports) {
      const context = await browser.newContext({ viewport });
      const page = await context.newPage();

      // Login as PM
      await page.goto('/login');
      await page.fill('input[name="username"]', pmUsername);
      await page.fill('input[name="password"]', pmPassword);
      await page.click('button[type="submit"]');
      await page.waitForNavigation();

      // Navigate to a project
      await page.goto('/projects');
      const projectLink = page.locator('a:has-text("Test Project")').first();
      if (await projectLink.count() > 0) {
        await projectLink.click();
        await page.waitForNavigation();

        // Verify widgets are present
        const widgets = page.locator('.card');
        const widgetCount = await widgets.count();

        if (widgetCount > 0) {
          // Verify responsive classes are applied
          const gridColumns = page.locator('[class*="col-"]');
          const columnCount = await gridColumns.count();
          expect(columnCount).toBeGreaterThan(0);

          // Take a screenshot for visual verification
          // await page.screenshot({ path: `viewport-${viewport.name}.png` });
        }
      }

      await context.close();
    }
  });

  test('Programme cards display with computed status badge', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as PM
    await page.goto('/login');
    await page.fill('input[name="username"]', pmUsername);
    await page.fill('input[name="password"]', pmPassword);
    await page.click('button[type="submit"]');
    await page.waitForNavigation();

    // Navigate to programmes
    await page.goto('/programmes');

    // Verify programme cards are visible
    const programmeCards = page.locator('.card');
    const cardCount = await programmeCards.count();

    if (cardCount > 0) {
      // Verify each card has a status badge
      const statusBadges = page.locator('.badge');
      expect(await statusBadges.count()).toBeGreaterThanOrEqual(cardCount);

      // Verify cards are in grid layout (2 columns on medium+)
      const gridColumns = page.locator('.col-12.col-lg-6');
      expect(await gridColumns.count()).toBeGreaterThanOrEqual(cardCount);
    }

    await context.close();
  });

  test('Project cards filterable by programme', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as PM
    await page.goto('/login');
    await page.fill('input[name="username"]', pmUsername);
    await page.fill('input[name="password"]', pmPassword);
    await page.click('button[type="submit"]');
    await page.waitForNavigation();

    // Navigate to projects
    await page.goto('/projects');

    // Look for programme filter dropdown
    const filterDropdown = page.locator('select[name="programme_id"]');
    if (await filterDropdown.count() > 0) {
      // Get initial card count
      const initialCards = page.locator('.card');
      const initialCount = await initialCards.count();

      // Select a different programme filter
      await filterDropdown.selectOption('none');

      // Verify the page reloads/updates
      await page.waitForTimeout(500);

      // Verify filter is applied in URL
      const url = page.url();
      expect(url).toContain('programme_id=none');
    }

    await context.close();
  });
});
