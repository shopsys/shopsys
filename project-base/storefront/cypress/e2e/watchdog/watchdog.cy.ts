import { staticData } from 'fixtures/demodata';
import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

describe('Watchdog Button Tests (SSP-4259)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Variants Table] should show exactly one watchdog button for each unavailable variant', () => {
        visitEntityByUuid('product', staticData.products.televisionHyundaiM.uuid);

        cy.getByTID([
            [TIDs.pages_productdetail_variant_, staticData.products.televisionHyundaiM.outOfStockVariantCatnum],
            TIDs.blocks_product_watchdog_button,
        ])
            .should('have.length', 1)
            .find('svg')
            .should('be.visible');
        cy.getByTID([
            [TIDs.pages_productdetail_variant_, staticData.products.televisionHyundaiM.outOfStockVariantCatnum],
            TIDs.blocks_product_addtocart,
        ]).should('not.exist');

        cy.getByTID([
            [TIDs.pages_productdetail_variant_, staticData.products.televisionHyundaiM.expectedRestockVariantCatnum],
            TIDs.blocks_product_watchdog_button,
        ]).should('have.length', 1);
        cy.getByTID([
            [TIDs.pages_productdetail_variant_, staticData.products.televisionHyundaiM.expectedRestockVariantCatnum],
            TIDs.blocks_product_addtocart,
        ]).should('have.length', 1);

        cy.getByTID([
            [TIDs.pages_productdetail_variant_, staticData.products.televisionHyundaiM.inStockVariantCatnum],
            TIDs.blocks_product_watchdog_button,
        ]).should('not.exist');
        cy.getByTID([
            [TIDs.pages_productdetail_variant_, staticData.products.televisionHyundaiM.inStockVariantCatnum],
            TIDs.blocks_product_addtocart,
        ]).should('have.length', 1);
    });
});
