import { changeSelectionOfPaymentByName, changeSelectionOfTransportByName } from './transportAndPaymentSupport';
import { DEFAULT_APP_STORE, payment, transport, url } from 'fixtures/demodata';
import { takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Payment select tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });

        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);
    });

    it('should select payment on delivery', () => {
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        cy.getByTID([TIDs.blocks_orderaction_next]).should('not.be.disabled');
        takeSnapshotAndCompare('payment-on-delivery');

        cy.getByTID([TIDs.blocks_orderaction_next]).click();
        cy.url().should('contain', url.order.contactInformation);
    });

    it('should select a payment, deselect it, and then change the payment option', () => {
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfPaymentByName(payment.creditCard.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        cy.getByTID([TIDs.blocks_orderaction_next]).should('not.be.disabled');
        takeSnapshotAndCompare('select-deselect-and-select-payment-again');

        cy.getByTID([TIDs.blocks_orderaction_next]).click();
        cy.url().should('contain', url.order.contactInformation);
    });

    it('should be able to remove payment using repeated clicks', () => {
        changeSelectionOfPaymentByName(payment.creditCard.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('remove-payment-selection-using-repeated-clicks_after-selecting');

        changeSelectionOfPaymentByName(payment.creditCard.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('remove-payment-selection-using-repeated-clicks_after-removing');
    });

    it('should be able to remove payment using reset button', () => {
        changeSelectionOfPaymentByName(payment.creditCard.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('remove-payment-selection-using-reset-button_after-selecting');

        cy.getByTID([TIDs.reset_payment_button]).click();

        takeSnapshotAndCompare('remove-payment-selection-using-reset-button_after-removing');
    });

    it('removing transport should remove payment as well, and then allow to select transport incompatible with previous payment', () => {
        changeSelectionOfPaymentByName(payment.creditCard.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('remove-payment-by-removing-transport_after-selecting-payment');

        cy.getByTID([TIDs.reset_transport_button]).click();

        takeSnapshotAndCompare('remove-payment-by-removing-transport_after-resetting-transport');

        changeSelectionOfTransportByName(transport.czechPost.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('remove-payment-by-removing-transport_after-selecting-incompatible-transport');
    });
});
