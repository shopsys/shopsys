import { staticData, url } from 'fixtures/demodata';
import {
    checkNumberOfApiRequestsTriggeredByActions,
    initializePersistStoreInLocalStorageToDefaultValues,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';
import { checkQueuedProductAdditions } from './productListsSupport';

describe('Comparison Race Condition Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Comparison Add - Rapid Click] should send only one AddProductToList request while button is processing', () => {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                cy.getByTID([TIDs.product_compare_button]).first().should('be.visible').focus();
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
            },
            1,
            'AddProductToListMutation',
        );
    });

    it('[Comparison Remove - Rapid Click] should send only one RemoveProductFromList request while button is processing', () => {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);

        cy.getByTID([TIDs.product_compare_button]).first().should('be.visible').click();
        cy.wait(1000);

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                cy.getByTID([TIDs.product_compare_button]).first().should('be.visible').focus();
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
            },
            1,
            'RemoveProductFromListMutation',
        );
    });

    it('[Comparison Add - Different Products Rapid Click] should add both products to the same new list', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        checkQueuedProductAdditions(TIDs.product_compare_button, url.productComparison, TIDs.comparison_product_);
    });
});
