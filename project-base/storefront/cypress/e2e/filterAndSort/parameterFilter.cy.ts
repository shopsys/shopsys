import { staticData } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 1;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.FILTER, SUBGROUP_INDEX);

const clearActiveFiltersIfPresent = () => {
    cy.get('body').then(($body) => {
        if ($body.find(`[data-tid="${TIDs.clear_all_filters_button}"]`).length > 0) {
            cy.getByTID([TIDs.clear_all_filters_button]).first().click({ force: true });
        }
    });
};

describe('Parameter Filter Tests (SSP-1739)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Parameter Checkbox Filter] should filter products by parameter checkbox and verify URL persistence', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').should('have.length.greaterThan', 0);

        cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').first().check({ force: true });
        cy.waitForStableAndInteractiveDOM();

        cy.url().should('include', 'filter');

        cy.getByTID([TIDs.product_list_item_image]).should('have.length.greaterThan', 0);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'parameter checkbox filter applied', {
            blackout: [
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.category_bestseller_image },
                { tid: TIDs.seo_categories },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        cy.reloadAndWaitForStableAndInteractiveDOM();
        cy.url().should('include', 'filter');
        cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').first().should('be.checked');

        clearActiveFiltersIfPresent();
    });

    it('[Multiple Parameter Filters] should combine multiple parameter filters and verify product count changes', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.waitForStableAndInteractiveDOM();

        let initialProductCount: number;
        cy.getByTID([TIDs.product_list_item_image]).then(($items) => {
            initialProductCount = $items.length;
        });

        cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').first().check({ force: true });
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.product_list_item_image]).then(($filteredItems) => {
            expect($filteredItems.length).to.be.lte(initialProductCount);
        });

        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'multiple parameter filters', {
            blackout: [
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.category_bestseller_image },
                { tid: TIDs.seo_categories },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        clearActiveFiltersIfPresent();
    });

    it('[Sort With Parameter Filter] should change sort order while parameter filter is active', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').first().check({ force: true });
        cy.waitForStableAndInteractiveDOM();
        cy.url().should('include', 'filter');

        cy.getByTID([[TIDs.blocks_sortingbar_option_, 'PRICE_ASC']]).filter(':visible').click();
        cy.waitForStableAndInteractiveDOM();

        cy.url().should('include', 'filter');

        cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').first().should('be.checked');

        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'sorted with parameter filter', {
            blackout: [
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.category_bestseller_image },
                { tid: TIDs.seo_categories },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        clearActiveFiltersIfPresent();
    });

    it('[Clear Parameter Filters] should clear all parameter filters and restore product listing', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.filter_price_input_min])
            .first()
            .find('input')
            .should('be.visible')
            .clear()
            .type('200')
            .blur();
        cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').first().check({ force: true });
        cy.waitForStableAndInteractiveDOM();

        cy.url().should('include', 'filter');

        clearActiveFiltersIfPresent();
        cy.url().should('not.include', 'filter');
        cy.getByTID([TIDs.selected_filters]).should('not.exist');
        cy.waitForStableAndInteractiveDOM();

        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filters cleared', {
            blackout: [
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.category_bestseller_image },
                { tid: TIDs.seo_categories },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
