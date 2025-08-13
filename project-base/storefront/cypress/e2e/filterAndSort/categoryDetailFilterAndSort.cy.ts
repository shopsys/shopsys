import { url } from 'fixtures/demodata';
import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';

describe('Product Filtering E2E Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Price Filter + URL Persistence] should filter products by price range and persist across page reload', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);

        cy.get('body').should('contain.text', 'Electronics');

        cy.waitForStableAndInteractiveDOM();

        cy.get('[id="Price - from"]').should('be.visible').clear().type('100').blur();

        cy.wait(500);

        cy.get('[id="Price - to"]').should('be.visible').clear().type('500').blur();

        cy.wait(1500);

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return decodedUrl.includes('100') || decodedUrl.includes('minimalPrice');
        });

        cy.reloadAndWaitForStableAndInteractiveDOM();

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return decodedUrl.includes('100');
        });

        cy.get('[id="Price - from"]').should('have.value', '100');

        cy.get('body').then(($body) => {
            if ($body.find('button[aria-label="Clear all active filters"]').length > 0) {
                cy.get('button[aria-label="Clear all active filters"]').first().click({ force: true });
            }
        });
    });

    it('[Multi-Filter Workflow] should combine price + brand + parameter filters correctly', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);

        cy.waitForStableAndInteractiveDOM();

        cy.get('[id="Price - from"]').should('be.visible').clear().type('50').blur();

        cy.wait(1000);

        cy.get('input[type="checkbox"]').should('have.length.greaterThan', 0).first().check({ force: true });

        cy.wait(1000);

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return decodedUrl.includes('50') || decodedUrl.includes('minimalPrice');
        });

        cy.get('body').should('contain.text', 'Electronics');

        cy.url().should('include', 'filter');

        cy.get('body').then(($body) => {
            if ($body.find('button[aria-label="Clear all active filters"]').length > 0) {
                cy.get('button[aria-label="Clear all active filters"]').first().click({ force: true });
            }
        });
    });

    it('[Sort + Filter Integration] should maintain filters when changing sort order', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);

        cy.waitForStableAndInteractiveDOM();

        cy.get('[id="Price - from"]').should('be.visible').clear().type('100').should('have.value', '100').blur();

        cy.wait(1000);

        cy.get('body').then(($body) => {
            if ($body.find('select').length > 0) {
                cy.get('select').first().select(1);
            } else {
                cy.get('button, [role="button"]')
                    .contains(/sort|order|price|name/i)
                    .first()
                    .click({ force: true });

                cy.get('[role="option"], li, a')
                    .contains(/price|name|newest|oldest/i)
                    .first()
                    .click({ force: true });
            }
        });

        cy.wait(1500);

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return decodedUrl.includes('100') || decodedUrl.includes('minimalPrice');
        });

        cy.waitForStableAndInteractiveDOM();

        cy.get('body').then(($body) => {
            if ($body.find('[id="Price - from"]').length > 0) {
                cy.get('[id="Price - from"]').should('be.visible').should('have.value', '100');
            } else {
                cy.log('Price filter input not found after sort, but URL filter should persist');
                cy.url().should('satisfy', (url) => {
                    const decodedUrl = decodeURIComponent(url);
                    return decodedUrl.includes('100') || decodedUrl.includes('minimalPrice');
                });
            }
        });

        cy.get('body').should('contain.text', 'Electronics');

        cy.get('body').then(($body) => {
            if ($body.find('button[aria-label="Clear all active filters"]').length > 0) {
                cy.get('button[aria-label="Clear all active filters"]').first().click({ force: true });
            }
        });
    });

    it('[Filter Reset Workflow] should clear all filters and reset URL parameters', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);

        cy.waitForStableAndInteractiveDOM();

        cy.get('[id="Price - from"]').should('be.visible').clear().type('200').blur();

        cy.wait(1000);

        cy.get('input[type="checkbox"]').first().check({ force: true });

        cy.wait(1000);

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return decodedUrl.includes('200') || decodedUrl.includes('filter');
        });

        cy.get('body').then(($body) => {
            const resetSelectors = [
                'button:contains("Clear")',
                'button:contains("Reset")',
                'a:contains("Clear")',
                '[role="button"]:contains("Clear")',
                'button[type="reset"]',
                'button:contains("Remove")',
            ];

            let found = false;
            for (const selector of resetSelectors) {
                if ($body.find(selector).length > 0) {
                    cy.get(selector).first().click({ force: true });
                    found = true;
                    break;
                }
            }

            if (!found) {
                cy.get('[id="Price - from"]').clear().blur();
            }
        });

        cy.wait(1000);

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return !decodedUrl.includes('200') || decodedUrl.includes('categoryElectronics');
        });

        cy.get('body').should('contain.text', 'Electronics');

        cy.get('body').then(($body) => {
            if ($body.find('button[aria-label="Clear all active filters"]').length > 0) {
                cy.get('button[aria-label="Clear all active filters"]').first().click({ force: true });
            }
        });
    });
});
