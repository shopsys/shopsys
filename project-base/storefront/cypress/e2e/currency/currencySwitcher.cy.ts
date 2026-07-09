import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';
import { TIDs } from 'tids';

describe('Currency Switcher Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('should switch prices to the secondary currency, persist it in a cookie and keep it after reload', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        cy.getByTID([TIDs.header_currency_switcher]).should('be.visible');

        cy.getByTID([[TIDs.header_currency_switcher_option_, 'CZK']]).click();
        cy.waitForStableAndInteractiveDOM();

        cy.getCookie('currencyCode-1').should('have.property', 'value', 'CZK');
        cy.getByTID([TIDs.product_price]).first().should('contain', 'CZK');

        cy.reload();
        cy.waitForStableAndInteractiveDOM();
        cy.getByTID([TIDs.product_price]).first().should('contain', 'CZK');

        cy.getByTID([[TIDs.header_currency_switcher_option_, 'EUR']]).click();
        cy.waitForStableAndInteractiveDOM();

        cy.getCookie('currencyCode-1').should('have.property', 'value', 'EUR');
        cy.getByTID([TIDs.product_price]).first().should('contain', '€');
    });
});
