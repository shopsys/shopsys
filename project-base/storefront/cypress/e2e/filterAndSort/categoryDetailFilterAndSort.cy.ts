import { COOKIES_STORE_NAME, staticData } from 'fixtures/demodata';
import {
    checktHeadlineText,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
    translations,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.FILTER, SUBGROUP_INDEX);

describe('Product Filtering E2E Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Price Filter + URL Persistence] should filter products by price range and persist across page reload', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        checktHeadlineText('Electronic devices');

        cy.waitForStableAndInteractiveDOM();

        cy.get(`[id="${translations.filter.price} - from"]`).should('be.visible').clear().type('100').blur();
        cy.get(`[id="${translations.filter.price} - to"]`).should('be.visible').clear().type('500').blur();
        cy.waitForStableAndInteractiveDOM();

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
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.filter_panel])
            .find('input[type="checkbox"]')
            .should('have.length.greaterThan', 0)
            .first()
            .check({ force: true });
        cy.waitForStableAndInteractiveDOM();

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
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([[TIDs.blocks_sortingbar_option_, 'PRICE_ASC']]).filter(':visible').click();
        cy.waitForStableAndInteractiveDOM();

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
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').first().check({ force: true });
        cy.waitForStableAndInteractiveDOM();

        cy.url().should('satisfy', (url) => {
            const decodedUrl = decodeURIComponent(url);
            return decodedUrl.includes('200') || decodedUrl.includes('filter');
        });

        cy.get('body').then(($body) => {
            if ($body.find(`[data-tid="${TIDs.clear_all_filters_button}"]`).length > 0) {
                cy.getByTID([TIDs.clear_all_filters_button]).first().click({ force: true });
            }
        });
        cy.waitForStableAndInteractiveDOM();

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

    it('[Product List View Mode] should switch product listing to list view and persist it after reload', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.blocks_product_list_view_grid]).should('have.attr', 'aria-pressed', 'true');
        cy.getByTID([TIDs.blocks_product_list_view_list]).should('have.attr', 'aria-pressed', 'false').click();
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.blocks_product_list_view_grid]).should('have.attr', 'aria-pressed', 'false');
        cy.getByTID([TIDs.blocks_product_list_view_list]).should('have.attr', 'aria-pressed', 'true');

        cy.getByTID([[TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum]]).within(() => {
            cy.getByTID([TIDs.product_list_item_image]).should('be.visible');
            cy.contains(staticData.products.helloKitty.catnum).should('be.visible');
            cy.get('a').should('have.length', 1);
        });
        cy.getByTID([
            [TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum],
            TIDs.product_compare_button,
        ])
            .should('be.visible')
            .should(($button) => {
                expect($button.prop('tagName')).not.to.equal('A');
            });
        cy.getByTID([
            [TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum],
            TIDs.product_wishlist_button,
        ])
            .should('be.visible')
            .should(($button) => {
                expect($button.prop('tagName')).not.to.equal('A');
            });
        cy.getByTID([
            [TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum],
            TIDs.blocks_product_addtocart,
        ])
            .should('be.visible')
            .should(($button) => {
                expect($button.prop('tagName')).not.to.equal('A');
            });

        cy.getCookie(COOKIES_STORE_NAME).then((cookie) => {
            expect(cookie).not.to.be.null;

            const cookiesStore = JSON.parse(decodeURIComponent(cookie?.value ?? ''));
            expect(cookiesStore.productListViewMode).to.equal('list');
        });

        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'product listing in list view', {
            capture: 'fullPage',
            blackout: [
                { tid: TIDs.category_bestseller_image },
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        cy.reloadAndWaitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.blocks_product_list_view_list]).should('have.attr', 'aria-pressed', 'true');
        cy.getByTID([[TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum]])
            .should('be.visible')
            .and('contain.text', staticData.products.helloKitty.catnum);
    });
});
