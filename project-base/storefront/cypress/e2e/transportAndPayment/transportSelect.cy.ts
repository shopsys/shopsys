import {
    changeDayOfWeekInChangeTransportMutationResponse,
    changeDayOfWeekInTransportsApiResponse,
    changeSelectionOfTransportByName,
    chooseTransportPersonalCollectionAndStore,
} from './transportAndPaymentSupport';
import { continueToTransportAndPaymentSelection, goBackToCartPage } from 'e2e/cart/cartSupport';
import { DEFAULT_APP_STORE, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import { checkLoaderOverlayIsNotVisible, checkUrl, loseFocus, takeSnapshotAndCompare } from 'support';
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

    it('should be able to remove transport using repeated clicks', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);
        changeSelectionOfTransportByName(transport.czechPost.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('remove-transport-selection-using-repeated-clicks_after-selecting');

        changeSelectionOfTransportByName(transport.czechPost.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('remove-transport-selection-using-repeated-clicks_after-removing');
    });

    it('should be able to remove transport using reset button', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);
        changeSelectionOfTransportByName(transport.czechPost.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('remove-transport-selection-using-reset-button_after-selecting');

        cy.getByTID([TIDs.reset_transport_button]).click();

        takeSnapshotAndCompare('remove-transport-selection-using-reset-button_after-removing');
    });

    it('should redirect to cart page and not display transport options if cart is empty and user is not logged in', () => {
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        cy.getByTID([TIDs.pages_order_transport]).should('not.exist');

        cy.getByTID([TIDs.cart_page_empty_cart_text]).should('exist');
        checkUrl(url.cart);

        takeSnapshotAndCompare('empty-cart-transport');
    });

    it('should redirect to cart page and not display transport options if cart is empty and user is logged in', () => {
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer'));
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        cy.getByTID([TIDs.pages_order_transport]).should('not.exist');

        cy.getByTID([TIDs.cart_page_empty_cart_text]).should('exist');
        checkUrl(url.cart);

        takeSnapshotAndCompare('empty-cart-transport-logged-in');
    });

    it('should change price for transport when cart is large enough for transport to be free', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        takeSnapshotAndCompare('free-transport-with-large-cart_before-free-in-cart');
        goBackToCartPage();
        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.spinbox_input]).type('00');
        loseFocus();
        checkLoaderOverlayIsNotVisible();
        takeSnapshotAndCompare('free-transport-with-large-cart_after-free-in-cart');

        continueToTransportAndPaymentSelection();
        changeSelectionOfTransportByName(transport.ppl.name);
        takeSnapshotAndCompare('free-transport-with-large-cart_after-free-transport-and-payment');
    });
});
