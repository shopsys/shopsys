import { DEFAULT_APP_STORE, url } from 'fixtures/demodata';
import { checkUrl, takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Contact information page tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should redirect to cart page and not display contact information form if cart is empty', () => {
        cy.visit(url.order.contactInformation);

        cy.getByTID([TIDs.order_content_wrapper_skeleton]).should('exist');

        cy.getByTID([TIDs.cart_page_empty_cart_text]).should('exist');
        checkUrl(url.cart);

        takeSnapshotAndCompare('empty-cart-contact-information');
    });

    it('should redirect to transport and payment select page and not display contact information form if transport and payment are not selected', () => {
        cy.addProductToCartForTest().then((cartUuid) => cy.storeCartUuidInLocalStorage(cartUuid));
        cy.visit(url.order.contactInformation);

        cy.getByTID([TIDs.order_content_wrapper_skeleton]).should('exist');

        cy.getByTID([TIDs.pages_order_transport]).should('exist');
        checkUrl(url.order.transportAndPayment);

        takeSnapshotAndCompare('no-transport-and-payment-in-contact-information');
    });
});
