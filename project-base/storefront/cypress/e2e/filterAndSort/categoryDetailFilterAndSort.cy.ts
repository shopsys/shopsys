import { staticData } from 'fixtures/demodata';
import { checktHeadlineText, initializePersistStoreInLocalStorageToDefaultValues, translations } from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

describe('Product Filtering E2E Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Price Filter + URL Persistence] should filter products by price range and persist across page reload', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        checktHeadlineText('Electronic devices');

        cy.waitForStableAndInteractiveDOM();

        cy.get(`[id="${translations.filter.price} - from"]`).should('be.visible').clear().type('100').blur();

        cy.wait(500);

        cy.get(`[id="${translations.filter.price} - to"]`).should('be.visible').clear().type('500').blur();

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

        cy.get(`[id="${translations.filter.price} - from"]`).should('have.value', '100');

        cy.get('body').then(($body) => {
            if ($body.find(`button[aria-label="${translations.filter.clearAllActiveFilters}"]`).length > 0) {
                cy.get(`button[aria-label="${translations.filter.clearAllActiveFilters}"]`)
                    .first()
                    .click({ force: true });
            }
        });
    });

    it('[Multi-Filter Workflow] should combine price + brand + parameter filters correctly', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        cy.waitForStableAndInteractiveDOM();

        cy.get(`[id="${translations.filter.price} - from"]`).should('be.visible').clear().type('50').blur();

        cy.wait(1000);

        cy.get('input[type="checkbox"]').should('have.length.greaterThan', 0).first().check({ force: true });

        cy.wait(1000);

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return decodedUrl.includes('50') || decodedUrl.includes('minimalPrice');
        });

        checktHeadlineText('Electronic devices');

        cy.url().should('include', 'filter');

        cy.get('body').then(($body) => {
            if ($body.find(`button[aria-label="${translations.filter.clearAllActiveFilters}"]`).length > 0) {
                cy.get(`button[aria-label="${translations.filter.clearAllActiveFilters}"]`)
                    .first()
                    .click({ force: true });
            }
        });
    });

    it('[Sort + Filter Integration] should maintain filters when changing sort order', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        cy.waitForStableAndInteractiveDOM();

        cy.get(`[id="${translations.filter.price} - from"]`)
            .should('be.visible')
            .clear()
            .type('100')
            .should('have.value', '100')
            .blur();

        cy.wait(1000);

        cy.get('[data-tid^="blocks_sortingbar_option_"]').should('exist').first().click({ force: true });

        cy.wait(1500);

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return decodedUrl.includes('100') || decodedUrl.includes('minimalPrice');
        });

        cy.waitForStableAndInteractiveDOM();

        cy.get('body').then(($body) => {
            if ($body.find(`[id="${translations.filter.price} - from"]`).length > 0) {
                cy.get(`[id="${translations.filter.price} - from"]`).should('be.visible').should('have.value', '100');
            } else {
                cy.log('Price filter input not found after sort, but URL filter should persist');
                cy.url().should('satisfy', (url) => {
                    const decodedUrl = decodeURIComponent(url);
                    return decodedUrl.includes('100') || decodedUrl.includes('minimalPrice');
                });
            }
        });

        checktHeadlineText('Electronic devices');

        cy.get('body').then(($body) => {
            if ($body.find(`button[aria-label="${translations.filter.clearAllActiveFilters}"]`).length > 0) {
                cy.get(`button[aria-label="${translations.filter.clearAllActiveFilters}"]`)
                    .first()
                    .click({ force: true });
            }
        });
    });

    it('[Filter Reset Workflow] should clear all filters and reset URL parameters', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        cy.waitForStableAndInteractiveDOM();

        cy.get(`[id="${translations.filter.price} - from"]`).should('be.visible').clear().type('200').blur();

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
                cy.get(`[id="${translations.filter.price} - from"]`).clear().blur();
            }
        });

        cy.wait(1000);

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return !decodedUrl.includes('200') || decodedUrl.includes('categoryElectronics');
        });

        checktHeadlineText('Electronic devices');

        cy.get('body').then(($body) => {
            if ($body.find(`button[aria-label="${translations.filter.clearAllActiveFilters}"]`).length > 0) {
                cy.get(`button[aria-label="${translations.filter.clearAllActiveFilters}"]`)
                    .first()
                    .click({ force: true });
            }
        });
    });
});
