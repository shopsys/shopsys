import { DEFAULT_APP_STORE, products } from 'fixtures/demodata';
import { takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Cart in header tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
        cy.addProductToCartForTest(undefined, 2).then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableDOM('/');
    });

    it('should remove products from cart using cart in header and then display empty cart message', () => {
        cy.getByTID([TIDs.header_cart_link]).realHover({ scrollBehavior: 'top' });
        cy.getByTID([TIDs.pages_cart_removecartitembutton]).first().click();
        cy.getByTID([TIDs.header_cart_link]).realHover();

        takeSnapshotAndCompare('remove-products-from-header-cart_after-first-remove', 'viewport');

        cy.getByTID([TIDs.header_cart_link]).realHover();
        cy.getByTID([TIDs.pages_cart_removecartitembutton]).click();
        cy.getByTID([TIDs.header_cart_link]).realHover();

        takeSnapshotAndCompare('remove-products-from-header-cart_after-second-remove', 'viewport');
    });
});
