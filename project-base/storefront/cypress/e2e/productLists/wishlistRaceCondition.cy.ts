import { staticData } from 'fixtures/demodata';
import {
    checkNumberOfApiRequestsTriggeredByActions,
    initializePersistStoreInLocalStorageToDefaultValues,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

describe('Wishlist Race Condition Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Wishlist Add - Rapid Click] should send only one AddProductToList request while button is processing', function () {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                cy.getByTID([TIDs.product_wishlist_button]).first().should('be.visible').focus();
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
            },
            1,
            'AddProductToListMutation',
        );
    });

    it('[Wishlist Remove - Rapid Click] should send only one RemoveProductFromList request while button is processing', function () {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);

        cy.getByTID([TIDs.product_wishlist_button]).first().should('be.visible').click();
        cy.wait(1000);

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                cy.getByTID([TIDs.product_wishlist_button]).first().should('be.visible').focus();
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
            },
            1,
            'RemoveProductFromListMutation',
        );
    });

    it('[Wishlist Add - Different Products Rapid Click] should send only one AddProductToList request when no list exists yet', function () {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                cy.getByTID([
                    [TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum],
                    TIDs.product_wishlist_button,
                ])
                    .should('be.visible')
                    .click();
                cy.getByTID([
                    [TIDs.blocks_product_list_listeditem_, staticData.products.philips32PFL4308.catnum],
                    TIDs.product_wishlist_button,
                ])
                    .should('be.visible')
                    .click();
            },
            1,
            'AddProductToListMutation',
        );
    });
});
