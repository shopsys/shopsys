import { openHeaderCartByHovering, removeFirstProductFromHeaderCart } from './cartSupport';
import { DEFAULT_APP_STORE, products } from 'fixtures/demodata';
import { takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Cart in header tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
        cy.addProductToCartForTest(products.helloKitty.uuid, 2).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('should remove products from cart using cart in header and then display empty cart message', function () {
        openHeaderCartByHovering();
        removeFirstProductFromHeaderCart();
        openHeaderCartByHovering();
        takeSnapshotAndCompare(this.test?.title, 'after first remove', {
            capture: 'viewport',
            wait: 2000,
            blackout: [{ tid: TIDs.banners_slider, zIndex: 5999 }],
        });

        openHeaderCartByHovering();
        removeFirstProductFromHeaderCart();
        openHeaderCartByHovering();
        takeSnapshotAndCompare(this.test?.title, 'after second remove', {
            capture: 'viewport',
            wait: 2000,
            blackout: [{ tid: TIDs.banners_slider, zIndex: 5999 }],
        });
    });
});
