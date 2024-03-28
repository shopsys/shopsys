import {
    changeDayOfWeekInChangeTransportMutationResponse,
    changeDayOfWeekInTransportsApiResponse,
    changeSelectionOfTransportByName,
    chooseTransportPersonalCollectionAndStore,
} from './transportAndPaymentSupport';
import { DEFAULT_APP_STORE, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import { checkUrl, takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Transport select tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should select transport to home', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);

        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        takeSnapshotAndCompare('transport-to-home');
    });

    it('should select personal pickup transport', () => {
        changeDayOfWeekInTransportsApiResponse(1);
        changeDayOfWeekInChangeTransportMutationResponse(1);
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        chooseTransportPersonalCollectionAndStore(transport.personalCollection.storeOstrava.name);

        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        takeSnapshotAndCompare('personal-pickup-transport');
    });

    it('should select a transport, deselect it, and then change the transport option', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfTransportByName(transport.czechPost.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfTransportByName(transport.ppl.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('select-deselect-and-select-transport-again');
    });

    it('should redirect to cart page and not display transport options if cart is empty and user is not logged in', () => {
        cy.visit(url.order.transportAndPayment);

        cy.getByTID([TIDs.order_content_wrapper_skeleton]).should('exist');

        cy.getByTID([TIDs.cart_page_empty_cart_text]).should('exist');
        checkUrl(url.cart);

        takeSnapshotAndCompare('empty-cart-transport');
    });

    it('should redirect to cart page and not display transport options if cart is empty and user is logged in', () => {
        cy.registerAsNewUser(generateCustomerRegistrationData());
        cy.visit(url.order.transportAndPayment);

        cy.getByTID([TIDs.order_content_wrapper_skeleton]).should('exist');

        cy.getByTID([TIDs.cart_page_empty_cart_text]).should('exist');
        checkUrl(url.cart);

        takeSnapshotAndCompare('empty-cart-transport-logged-in');
    });
});
